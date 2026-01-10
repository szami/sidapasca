<?php

namespace App\Controllers;

use App\Utils\Database;
use App\Utils\SchemaDefinition;
use App\Utils\View;
use App\Utils\RoleHelper;

class MigrationController
{
    public function index()
    {
        if (!RoleHelper::isSuperadmin()) {
            response()->redirect('/admin?error=unauthorized');
            return;
        }

        $db = Database::connection();
        $expected = SchemaDefinition::getExpectedSchema();
        $status = [];

        // Get actual tables
        try {
            // For SQLite
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
            $existing_tables = array_column($tables, 'name');

            // If MySQL, query would be different, but app seems SQLite based on migrate.php
        } catch (\Exception $e) {
            // Fallback or error
            $existing_tables = [];
        }

        foreach ($expected as $table => $def) {
            if (in_array($table, $existing_tables)) {
                // Table exists, check data
                if (SchemaDefinition::hasDataMismatch($table, $db)) {
                    $status[$table] = 'DATA_MISMATCH';
                } else {
                    $status[$table] = 'OK';
                }
            } else {
                $status[$table] = 'MISSING';
            }
        }

        echo View::render('admin.tools.migration', ['status' => $status]);
    }

    public function sync()
    {
        if (!RoleHelper::isSuperadmin()) {
            response()->json(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $table = $_POST['table'] ?? null;
        if (!$table) {
            response()->json(['success' => false, 'message' => 'No table specified']);
            return;
        }

        $expected = SchemaDefinition::getExpectedSchema();
        if (!isset($expected[$table])) {
            response()->json(['success' => false, 'message' => 'Unknown table']);
            return;
        }

        $sql = $expected[$table]['create_sql'];
        $msg = "";

        try {
            $db = Database::connection();

            // Check if table exists
            $exists = false;
            try {
                $check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetchAssoc();
                if ($check)
                    $exists = true;
            } catch (\Exception $e) {
            }

            if (!$exists) {
                // Create Table
                $db->query($sql)->execute();
                $msg .= "Table $table created. ";
            }

            // Run Seeder if available
            $seeder = SchemaDefinition::getSeeder($table);
            if ($seeder) {
                // Execute Closure
                $seedMsg = $seeder($db);
                $msg .= $seedMsg;
            } else {
                $msg .= "No seeder defined.";
            }

            response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
