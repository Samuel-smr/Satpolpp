<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PelaporController extends Controller
{
    public function index()
    {
        return view('pelapor.index');
    }

    public function lapor()
    {
        return view('pelapor.lapor');
    }

    public function status()
    {
        return view('pelapor.status');
    }

    public function feedback()
    {
        return view('pelapor.feedback');
    }
}
