<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index');
    }

    public function kasir()
    {
        return view('kasir.dashboard.index');
    }

    public function super()
    {
        return view('super.dashboard.index');
    }
}
