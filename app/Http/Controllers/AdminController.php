<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function pengaduan()
    {
        return view('admin.pengaduan');
    }

    public function kegiatan()
    {
        return view('admin.kegiatan');
    }

    public function feedback()
    {
        return view('admin.feedback');
    }

    public function arsip()
    {
        return view('admin.arsip');
    }

    public function aset()
    {
        return view('admin.aset');
    }

    public function peta()
    {
        return view('admin.peta');
    }
}
