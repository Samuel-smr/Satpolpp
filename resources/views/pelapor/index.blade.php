@extends('layouts.pelapor')

@section('title', 'Beranda')

@section('content')
    {{-- ===== HERO ===== --}}
    <section class="relative -mt-8 -mb-8 overflow-hidden bg-brand-700 text-white" style="width:100vw; margin-left:calc(50% - 50vw); margin-right:calc(50% - 50vw);">
        {{-- Dekorasi latar --}}
        <div class="absolute inset-0 bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800"></div>
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 26px 26px;"></div>
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 w-80 h-80 bg-accent-500/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">
            <div class="lg:max-w-none">
                <span class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-5 py-2 text-base font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-accent-400 animate-pulse"></span>
                    Layanan resmi Satuan Polisi Pamong Praja
                </span>

                <h1 class="mt-6 font-headline text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-tight">
                    Laporkan Gangguan Ketertiban <span class="text-accent-400">Dengan Cepat.</span>
                </h1>

                <p class="mt-6 text-brand-100 text-lg md:text-xl max-w-3xl leading-relaxed">
                    SIPRAJA mempermudah masyarakat melaporkan gangguan ketertiban umum dan keamanan.
                    Tanpa registrasi akun, langsung pantau perkembangan laporan Anda.
                </p>

                <div class="mt-9">
                    <a href="{{ route('pelapor.lapor') }}"
                       class="inline-flex items-center justify-center gap-2 bg-accent-500 text-white px-9 py-4 rounded-lg font-semibold shadow-lg shadow-accent-500/30 hover:bg-accent-600 hover:shadow-accent-600/30 transition-all text-xl">
                        <span class="text-2xl">🚨</span> Lapor Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== LAYANAN MASYARAKAT ===== --}}
    <section class="py-16 md:py-20">
        <div class="text-center max-w-2xl mx-auto mb-10 md:mb-12 px-4">
            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-accent-600 bg-orange-50 border border-orange-100 rounded-full px-3 py-1 mb-3">Layanan</span>
            <h2 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Layanan Masyarakat</h2>
            <p class="mt-3 text-slate-500">Pilih layanan yang Anda butuhkan. Tanpa perlu membuat akun.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 px-0">
            {{-- Lapor Darurat --}}
            <a href="{{ route('pelapor.lapor', ['jenis' => 'darurat']) }}"
               class="group relative bg-white border border-slate-200 rounded-2xl p-6 md:p-7 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-accent-300 transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-accent-600 flex items-center justify-center text-2xl mb-4">🚨</div>
                    <h3 class="font-headline text-lg font-bold text-slate-800 group-hover:text-accent-600 transition-colors">Lapor Darurat</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">Kejadian yang membutuhkan respons cepat seperti kerusuhan, kebakaran, atau gangguan ketertiban.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-accent-600">
                        Lapor sekarang
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </span>
                </div>
            </a>

            {{-- Informasi & Pengaduan --}}
            <a href="{{ route('pelapor.lapor', ['jenis' => 'informasi']) }}"
               class="group relative bg-white border border-slate-200 rounded-2xl p-6 md:p-7 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-brand-300 transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl mb-4">📋</div>
                    <h3 class="font-headline text-lg font-bold text-slate-800 group-hover:text-brand-600 transition-colors">Informasi &amp; Pengaduan</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">Laporan yang tidak membutuhkan respons darurat, misalnya PKL liar, kebisingan, atau ketertiban umum.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600">
                        Lapor sekarang
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </span>
                </div>
            </a>

            {{-- Cek Status --}}
            <a href="{{ route('pelapor.status') }}"
               class="group relative bg-white border border-slate-200 rounded-2xl p-6 md:p-7 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-brand-300 transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl mb-4">🔎</div>
                    <h3 class="font-headline text-lg font-bold text-slate-800 group-hover:text-brand-600 transition-colors">Cek Status Laporan</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">Pantau perkembangan laporan Anda menggunakan nomor / kode pengaduan secara real-time.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600">
                        Cek sekarang
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </span>
                </div>
            </a>

            {{-- Survey Kepuasan --}}
            <a href="{{ route('pelapor.feedback') }}"
               class="group relative bg-white border border-slate-200 rounded-2xl p-6 md:p-7 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-brand-300 transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl mb-4">⭐</div>
                    <h3 class="font-headline text-lg font-bold text-slate-800 group-hover:text-brand-600 transition-colors">Survey Kepuasan</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">Isi survey kepuasan untuk membantu kami terus meningkatkan layanan.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600">
                        Survey sekarang
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </span>
                </div>
            </a>
        </div>
    </section>

    {{-- ===== CARA MELAPOR ===== --}}
    <section class="bg-white border-y border-slate-200 py-16 md:py-20">
        <div class="text-center max-w-2xl mx-auto mb-10 md:mb-14 px-4">
            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-brand-600 bg-brand-50 border border-brand-100 rounded-full px-3 py-1 mb-3">Langkah</span>
            <h2 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Cara Melapor</h2>
            <p class="mt-3 text-slate-500">Hanya 4 langkah mudah untuk menyampaikan laporan Anda.</p>
        </div>

        <div class="max-w-5xl mx-auto px-4">
            <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                {{-- Garis penghubung --}}
                <div class="hidden lg:block absolute top-6 left-[12%] right-[12%] h-px bg-slate-200"></div>

                @php
                    $steps = [
                        ['1', '📝', 'Pilih Jenis Laporan', 'Darurat atau Informasi & Pengaduan'],
                        ['2', '📍', 'Isi Detail & Lokasi', 'Sertakan foto/video bila ada'],
                        ['3', '📤', 'Kirim', 'Laporan Anda terkirim'],
                        ['4', '📊', 'Pantau Status', 'Cek perkembangan & isi survey kepuasan'],
                    ];
                @endphp

                @foreach ($steps as $i => [$num, $icon, $title, $desc])
                    <div class="relative flex flex-col items-center text-center">
                        <div class="relative z-10 w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl shadow-lg shadow-brand-600/25">
                            {{ $icon }}
                        </div>
                        <span class="mt-4 text-xs font-semibold uppercase tracking-wider text-brand-600">Langkah {{ $num }}</span>
                        <h4 class="mt-1 font-semibold text-slate-800">{{ $title }}</h4>
                        <p class="mt-1 text-sm text-slate-500 max-w-[180px]">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== STATISTIK PUBLIK ===== --}}
    <section class="py-16 md:py-20">
        <div class="max-w-5xl mx-auto px-4">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 md:p-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl mb-3">📝</div>
                        <div class="font-headline text-3xl md:text-4xl font-bold text-slate-800">12,450</div>
                        <div class="mt-1 text-xs font-medium text-slate-500 uppercase tracking-wider">Total Laporan</div>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-3">✅</div>
                        <div class="font-headline text-3xl md:text-4xl font-bold text-slate-800">98%</div>
                        <div class="mt-1 text-xs font-medium text-slate-500 uppercase tracking-wider">Tingkat Penyelesaian</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== LAYANAN DARURAT ===== --}}
    <section class="pb-16 md:pb-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="relative overflow-hidden bg-gradient-to-r from-red-600 to-red-700 text-white rounded-2xl p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6 shadow-lg shadow-red-600/20">
                <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative flex items-center gap-4 md:gap-6">
                    <div class="bg-white/15 border border-white/20 p-4 rounded-2xl">
                        <div class="text-3xl">🚨</div>
                    </div>
                    <div>
                        <h2 class="font-headline text-xl md:text-2xl font-bold">Layanan Darurat Ketertiban Umum</h2>
                        <p class="text-red-100 mt-1">Tersedia 24 jam / 7 hari seminggu</p>
                    </div>
                </div>
                <a href="{{ route('pelapor.lapor', ['jenis' => 'darurat']) }}" class="relative w-full md:w-auto text-center bg-white text-red-600 font-semibold px-8 py-3 rounded-lg hover:bg-red-50 hover:shadow-lg transition-all">
                    Lapor Sekarang
                </a>
            </div>
        </div>
    </section>
@endsection
