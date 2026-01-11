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

        echo View::render('admin.tools.migration', [
            'status' => $status,
            'patches' => $this->getPatches()
        ]);
    }

    private function getPatches()
    {
        $baseDir = dirname(__DIR__, 2); // app -> Controllers -> (root) -> app? No.
        // __DIR__ is app/Controllers. dirname is app. dirname 2 is root?
        // Let's assume root is correct based on other usage. e.g. dirname(__DIR__, 2) from Controller usually lands in app/.. -> root?
        // Wait, standard structure: e:\laragon\www\pmb-pps-ulm\app\Controllers
        // dirname(.., 1) = app
        // dirname(.., 2) = pmb-pps-ulm (Root)

        $migrationDir = dirname(__DIR__, 2) . '/app/database/migrations';
        $files = glob($migrationDir . '/*.php');
        $patches = [];

        foreach ($files as $file) {
            $name = basename($file);
            // Exclude core migration files
            $excludes = ['migrate.php', 'seed.php', 'skm_migration.php', 'remove_redundant_fields.php'];

            if (!in_array($name, $excludes)) {
                $patches[] = [
                    'filename' => $name,
                    'path' => $file,
                    'is_php' => true
                ];
            }
        }

        // Also check manual sql files if needed, but for now just PHP scripts that do logic
        return $patches;
    }

    public function runPatch()
    {
        if (!RoleHelper::isSuperadmin()) {
            response()->json(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $filename = $_POST['filename'] ?? null;
        if (!$filename) {
            response()->json(['success' => false, 'message' => 'No file specified']);
            return;
        }

        // Security: Prevent Directory Traversal
        $filename = basename($filename);
        $baseDir = dirname(__DIR__, 2);
        $filePath = $baseDir . '/app/database/migrations/' . $filename;

        if (!file_exists($filePath)) {
            response()->json(['success' => false, 'message' => 'File not found']);
            return;
        }

        // Capture Output
        ob_start();
        try {
            // Include closure to isolate scope slightly, but mostly just include
            include $filePath;
            $output = ob_get_clean();
            response()->json(['success' => true, 'message' => $output]);
        } catch (\Throwable $e) {
            $output = ob_get_clean();
            response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage() . "\nOutput: " . $output]);
        }
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
