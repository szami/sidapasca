<?php

namespace App\Utils;

class View
{
    public static function render($view, $data = [])
    {
        // Convert dot notation to path
        $viewPath = __DIR__ . '/../../app/views/' . str_replace('.', '/', $view) . '.blade.php';

        if (!file_exists($viewPath)) {
            // Try standard .php
            $viewPath = __DIR__ . '/../../app/views/' . str_replace('.', '/', $view) . '.php';
            if (!file_exists($viewPath)) {
                return "View not found: $view";
            }
        }

        // Extract data to variables
        extract($data);

        // Start buffer
        ob_start();

        // Include file
        // Note: Blade directives (@extends, @section) won't work in raw PHP.
        // We need a simple parser or just ignore them for now and use raw PHP in views?
        // The views I wrote USE Blade directives.
        // So I really need Blade to work or rewrite views to PHP.
        // Rewriting views to PHP is tedious.

        // Alternative: Fix Blade.
        // Let's see migration output first.

        include $viewPath;

        return ob_get_clean();
    }
}
