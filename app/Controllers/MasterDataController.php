<?php

namespace App\Controllers;

use App\Utils\View;

class MasterDataController
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
        echo View::render('admin.master.hub');
    }
}
