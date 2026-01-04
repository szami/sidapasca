<?php

namespace App\Controllers;

use App\Utils\View;

class HomeController
{
    public function index()
    {
        echo View::render('home.index');
    }
}
