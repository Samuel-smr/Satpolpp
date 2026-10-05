@extends('layouts.super_admin')

@section('title', 'Manajemen Petugas')

@section('content')
    <div class="mb-8">
        <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Daftar Pegawai</h1>
        <p class="mt-1 text-sm text-slate-500">Nama, NIP, dan jabatan pegawai mengikuti data e-Profil.</p>
    </div>

    @if ($officerDirectoryWarning)
        <p role="alert" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ $officerDirectoryWarning }}</p>
    @endif

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Total Pegawai</p>
        <p class="mt-1 font-headline text-3xl font-bold text-slate-900">{{ $officers->count() }}</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="text-sm font-semibold text-slate-700">Daftar Pegawai</span>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">e-Profil</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3 font-semibold">NIP</th>
                        <th class="px-6 py-3 font-semibold">Nama</th>
                        <th class="px-6 py-3 font-semibold">Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officers as $officer)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-500">{{ $officer->employee_number }}</td>
                            <td class="px-6 py-3.5 font-medium text-slate-800">{{ $officer->name }}</td>
                            <td class="min-w-80 px-6 py-3.5 text-slate-500">{{ $officer->position }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-slate-400">Belum ada data pegawai dari e-Profil.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
