@extends('layouts.admin')

@section('title', 'Feedback Masyarakat')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Feedback &amp; Kepuasan Masyarakat</h1>
            <p class="text-sm text-slate-500 mt-1">Ulasan, tingkat kepuasan, dan saran dari warga terhadap penanganan laporan.</p>
        </div>
    </div>

    {{-- Ringkasan Kepuasan --}}
    @php
        $summaries = [
            ['label' => 'Total Feedback', 'value' => '128', 'icon' => 'chat', 'color' => 'text-brand-600 bg-brand-50'],
            ['label' => 'Rata-rata Rating', 'value' => '4,6 / 5', 'icon' => 'star', 'color' => 'text-amber-600 bg-amber-50'],
            ['label' => 'Sangat Puas (5★)', 'value' => '86', 'icon' => 'sentiment_very_satisfied', 'color' => 'text-emerald-600 bg-emerald-50'],
            ['label' => 'Perlu Perbaikan (&lt;3★)', 'value' => '9', 'icon' => 'sentiment_dissatisfied', 'color' => 'text-red-600 bg-red-50'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($summaries as $s)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl {{ $s['color'] }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">{{ $s['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium">{{ $s['label'] }}</p>
                        <p class="font-headline text-2xl font-bold text-slate-900">{{ $s['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Daftar Feedback --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Feedback Pelapor</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy</span>
        </div>

        <div class="divide-y divide-slate-100">
            @php
                $feedbacks = [
                    [
                        'kode' => 'SPJ-583990', 'jenis' => 'Informasi & Pengaduan', 'kategori' => 'Kebersihan Lingkungan',
                        'rating' => 5, 'waktu' => '2 hari lalu',
                        'komentar' => 'Petugas cepat dan sigap. Tumpukan sampah di pinggir jalan utama sudah dibersihkan. Terima kasih!',
                    ],
                    [
                        'kode' => 'SPJ-584021', 'jenis' => 'Lapor Darurat', 'kategori' => 'Ketertiban Umum',
                        'rating' => 4, 'waktu' => '3 hari lalu',
                        'komentar' => 'Kerumunan massa di alun-alun sudah ditangani dengan baik. Responnya cukup cepat.',
                    ],
                    [
                        'kode' => 'SPJ-583772', 'jenis' => 'Informasi & Pengaduan', 'kategori' => 'Keamanan',
                        'rating' => 2, 'waktu' => '4 hari lalu',
                        'komentar' => 'Laporan orang mabuk di pasar ditangani, tapi kurang cepat. Semoga bisa lebih responsif lagi.',
                    ],
                    [
                        'kode' => 'SPJ-584100', 'jenis' => 'Lapor Darurat', 'kategori' => 'Keamanan',
                        'rating' => 5, 'waktu' => '1 minggu lalu',
                        'komentar' => 'Sangat puas dengan penanganan petugas. Profesional, ramah, dan memberi penjelasan yang jelas.',
                    ],
                    [
                        'kode' => 'SPJ-583850', 'jenis' => 'Informasi & Pengaduan', 'kategori' => 'Ketertiban Umum',
                        'rating' => 3, 'waktu' => '1 minggu lalu',
                        'komentar' => 'Penanganan cukup baik meskipun butuh waktu. Perlu perbaikan pada komunikasi kelanjutan laporan.',
                    ],
                ];
            @endphp

            @foreach ($feedbacks as $f)
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-800">{{ $f['kode'] }}</span>
                                <span class="text-xs font-semibold {{ $f['jenis'] === 'Lapor Darurat' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }} px-2.5 py-1 rounded-full">{{ $f['jenis'] }}</span>
                                <span class="text-xs text-slate-400">{{ $f['waktu'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-amber-400 text-sm">
                                    @for ($i = 1; $i <= 5; $i++)
                                        {{ $i <= $f['rating'] ? '★' : '☆' }}
                                    @endfor
                                </span>
                                <span class="text-xs text-slate-400">({{ $f['rating'] }}/5)</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="text-sm font-medium text-slate-700">{{ $f['kategori'] }}</p>
                        <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ $f['komentar'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
