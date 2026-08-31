@extends('layouts.super_admin')

@section('title', 'Manajemen Petugas')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Data Petugas</h1>
            <p class="text-sm text-slate-500 mt-1">Direktori seluruh personil Satpol PP, regu lapangan, dan nomor NIP/KTA.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <span class="text-sm font-semibold text-slate-700">Tampilan Sementara (Super Admin Prototype)</span>
            <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium">Backend menyusul</span>
        </div>
        <p class="text-sm text-slate-500 leading-relaxed">
            Halaman ini dipersiapkan untuk pendataan personel aktif, regu patroli, status tugas lapangan, dan riwayat penugasan.
        </p>
    </div>
@endsection
