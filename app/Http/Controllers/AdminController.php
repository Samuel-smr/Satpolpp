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

    public function pengaduanDetail($kode)
    {
        return view('admin.pengaduan-detail', compact('kode'));
    }

    public function kegiatan()
    {
        return view('admin.kegiatan');
    }

    public function feedback()
    {
        return view('admin.feedback');
    }

    public function evaluasi()
    {
        return view('admin.evaluasi');
    }

    public function arsip()
    {
        return view('admin.arsip');
    }

    public function aset()
    {
        return view('admin.aset');
    }

    public function profile()
    {
        return view('admin.profile');
    }
}
