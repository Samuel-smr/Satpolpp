<?php

namespace App\Http\Controllers;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        return view('super_admin.dashboard');
    }

    public function kegiatan()
    {
        return view('super_admin.kegiatan');
    }

    public function user()
    {
        return view('super_admin.user');
    }

    public function organisasi()
    {
        return view('super_admin.organisasi');
    }

    public function petugas()
    {
        return view('super_admin.petugas');
    }

    public function monitoring()
    {
        return view('super_admin.monitoring');
    }

    public function peta()
    {
        return view('super_admin.peta');
    }
}
