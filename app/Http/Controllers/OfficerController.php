<?php

namespace App\Http\Controllers;

use App\Services\EprofilService;
use Illuminate\View\View;

class OfficerController extends Controller
{
    public function index(EprofilService $eprofil): View
    {
        $officers = $eprofil->officers();

        return view('super_admin.petugas', [
            'officers' => $officers,
            'officerDirectoryWarning' => $eprofil->warning(),
        ]);
    }
}
