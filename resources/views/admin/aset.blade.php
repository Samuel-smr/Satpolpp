@extends('layouts.admin')

@section('title', 'Aset Operasional')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Inventaris Aset Operasional</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar sarana, prasarana, kendaraan dinas, dan perlengkapan Satpol PP.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <span class="text-sm font-semibold text-slate-700">Tampilan Sementara (Frontend Prototype)</span>
            <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium">Backend menyusul</span>
        </div>
        <p class="text-sm text-slate-500 leading-relaxed">
            Halaman ini dipersiapkan untuk inventarisasi fisik aset, pemantauan kondisi kendaraan operasional, dan log pemeliharaan.
        </p>
    </div>
@endsection
