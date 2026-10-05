<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;

class Attendance extends BaseController
{
    public function index()
    {
        $dashboard = new \App\Controllers\Teacher\Dashboard();
        return $dashboard->attendance();
    }
}