@extends('layouts.admin')

@section('title', 'Pengaduan')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Daftar Pengaduan Warga</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan tinjau semua pengaduan yang masuk dari masyarakat.</p>
        </div>
        <div>
            <button type="button" class="px-4 py-2.5 bg-accent-600 hover:bg-accent-500 rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Tambah Pengaduan</span>
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <span class="text-sm font-semibold text-slate-700">Tampilan Sementara (Frontend Prototype)</span>
            <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium">Backend menyusul</span>
        </div>
        <p class="text-sm text-slate-500 leading-relaxed">
            Halaman ini dipersiapkan untuk menampilkan tabel daftar pengaduan, filter status, pencarian, dan opsi tindakan penanganan.
        </p>
    </div>
@endsection
