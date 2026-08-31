@extends('layouts.admin')

@section('title', 'Peta Persebaran')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Peta Persebaran Pengaduan &amp; Personel</h1>
            <p class="text-sm text-slate-500 mt-1">Pemetaan lokasi kejadian, titik rawan gangguan, dan posisi regu lapangan.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <span class="text-sm font-semibold text-slate-700">Tampilan Sementara (Frontend Prototype)</span>
            <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium">Backend menyusul</span>
        </div>
        <p class="text-sm text-slate-500 leading-relaxed mb-4">
            Halaman ini dipersiapkan untuk peta spasial (GIS/Geospatial Map) interaktif yang menampilkan klaster lokasi laporan warga dan pergerakan regu patroli.
        </p>
        <div class="w-full h-64 bg-slate-100 border border-dashed border-slate-300 rounded-xl flex items-center justify-center text-slate-400 font-medium">
            <div class="flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-4xl text-slate-400">map</span>
                <span>Peta Spasial Interaktif GIS</span>
            </div>
        </div>
    </div>
@endsection
