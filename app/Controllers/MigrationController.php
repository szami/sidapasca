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
                $status[$table] = 'EXISTS';
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

        try {
            $db = Database::connection();
            $db->query($sql)->execute();
            response()->json(['success' => true, 'message' => "Table $table synced successfully."]);
        } catch (\Exception $e) {
            response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
