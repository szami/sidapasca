<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\View;

class SystemController
{
    public function update()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        echo View::render('admin.system.update');
    }

    public function performUpdate()
    {
        if (!isset($_SESSION['admin']))
            return;

        // Logic for update (e.g. git pull)
        // For now, just simulate
        // exec('git pull origin main', $output);

        response()->redirect('/admin/system/update?status=success');
    }
}
