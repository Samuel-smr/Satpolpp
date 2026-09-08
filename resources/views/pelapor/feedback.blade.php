@extends('layouts.pelapor')

@section('title', 'Survey Kepuasan')

@php
    $surveyUrl = 'https://skm.go.id/share/instansi/f1319c33-2d84-4f64-bcdb-e73685895e37/1';
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($surveyUrl);
@endphp

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Survey Kepuasan</h1>
            <p class="mt-2 text-slate-500">Bantu kami meningkatkan kualitas layanan dengan mengisi survey kepuasan.</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm text-center">
            {{-- QR Code --}}
            <div class="inline-block bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                <img src="{{ $qrUrl }}" alt="QR Code Survey Kepuasan" class="w-56 h-56 rounded-lg" width="224" height="224">
            </div>
            <p class="text-sm text-slate-500 mb-2">Scan QR di atas untuk membuka survey</p>

            {{-- Link --}}
            <p class="text-xs text-slate-400 mb-6 break-all">{{ $surveyUrl }}</p>

            {{-- Tombol Survey Sekarang --}}
            <a href="{{ $surveyUrl }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 w-full bg-brand-600 text-white py-3.5 rounded-xl font-semibold hover:bg-brand-700 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">assignment</span>
                Survey Sekarang
            </a>

            <p class="mt-4 text-xs text-slate-400">Anda akan diarahkan ke halaman survey resmi (SKM) di tab baru.</p>
        </div>
    </div>
@endsection
