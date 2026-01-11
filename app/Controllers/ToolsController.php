<?php

namespace App\Controllers;

use App\Utils\View;

class ToolsController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit;
        }
    }

    public function hub()
    {
        $this->checkAuth();
        echo View::render('admin.tools.index');
    }
}
