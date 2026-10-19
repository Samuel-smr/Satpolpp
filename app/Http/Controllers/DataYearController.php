<?php

namespace App\Http\Controllers;

use App\Support\DataYear;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataYearController extends Controller
{
    public function update(Request $request, DataYear $dataYear): RedirectResponse
    {
        $validated = $request->validate([
            'data_year' => ['required', 'integer', Rule::in($dataYear->available())],
        ], ['data_year.in' => 'Tahun data tidak tersedia.']);

        $request->session()->put('data_year', (int) $validated['data_year']);

        return redirect()->route($request->user()->role === UserRole::SuperAdmin ? 'super_admin.dashboard' : 'admin.dashboard');
    }
}
