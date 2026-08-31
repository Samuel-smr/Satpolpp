@extends('layouts.pelapor')

@section('title', 'Beranda')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-brand-700 text-white -mt-8">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 24px 24px;"></div>
        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-20 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div>
                <h1 class="font-headline text-3xl md:text-5xl font-bold leading-tight">
                    Sistem Pengaduan Masyarakat Satpol PP
                </h1>
                <p class="mt-4 text-brand-100 text-lg max-w-xl">
                    SIPRAJA mempermudah masyarakat melaporkan gangguan ketertiban umum dan keamanan.
                    Laporkan dengan cepat, pantau perkembangannya, dan berikan penilaian atas layanan kami.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('pelapor.lapor', ['jenis' => 'darurat']) }}"
                       class="inline-flex items-center justify-center gap-2 bg-accent-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-accent-600 transition-colors">
                        🚨 Lapor Darurat
                    </a>
                    <a href="{{ route('pelapor.lapor', ['jenis' => 'informasi']) }}"
                       class="inline-flex items-center justify-center gap-2 border-2 border-brand-300 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-600 transition-colors">
                        📋 Informasi &amp; Pengaduan
                    </a>
                </div>
            </div>

            {{-- Panel status cepat --}}
            <div class="hidden md:block">
                <div class="bg-white/95 backdrop-blur rounded-xl p-6 shadow-xl text-slate-800">
                    <div class="font-semibold text-slate-500 mb-4 flex items-center gap-2">🔔 Laporan Terkini</div>
                    <div class="space-y-4">
                        <div class="border-l-4 border-accent-500 pl-3">
                            <p class="text-xs text-slate-400">Baru saja</p>
                            <p class="text-sm text-slate-700">Pelanggaran ketertiban — PKL liar trotoar jalan utama</p>
                        </div>
                        <div class="border-l-4 border-brand-500 pl-3">
                            <p class="text-xs text-slate-400">10 menit lalu</p>
                            <p class="text-sm text-slate-700">Gangguan ketertiban — suara bising di permukiman</p>
                        </div>
                        <div class="border-l-4 border-brand-500 pl-3">
                            <p class="text-xs text-slate-400">25 menit lalu</p>
                            <p class="text-sm text-slate-700">Keamanan — orang mabuk di area publik</p>
                        </div>
                    </div>
                    <a href="{{ route('pelapor.status') }}" class="mt-5 block text-center text-sm text-brand-600 font-medium hover:underline">Cek status laporan Anda →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Menu Utama --}}
    <section class="py-12">
        <div class="text-center mb-8">
            <h2 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Layanan Masyarakat</h2>
            <p class="mt-2 text-slate-500 max-w-2xl mx-auto">Pilih layanan yang Anda butuhkan. Tanpa perlu registrasi akun.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('pelapor.lapor', ['jenis' => 'darurat']) }}"
               class="group bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md hover:border-accent-500 transition-all">
                <div class="text-3xl mb-3">🚨</div>
                <h3 class="font-headline font-bold text-lg text-slate-800 group-hover:text-accent-600">Lapor Darurat</h3>
                <p class="mt-1 text-sm text-slate-500">Kejadian yang membutuhkan respons cepat seperti kerusuhan, kebakaran, atau gangguan ketertiban.</p>
            </a>

            <a href="{{ route('pelapor.lapor', ['jenis' => 'informasi']) }}"
               class="group bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md hover:border-brand-500 transition-all">
                <div class="text-3xl mb-3">📋</div>
                <h3 class="font-headline font-bold text-lg text-slate-800 group-hover:text-brand-600">Informasi &amp; Pengaduan</h3>
                <p class="mt-1 text-sm text-slate-500">Laporan yang tidak membutuhkan respons darurat, misalnya PKL liar, kebisingan, atau ketertiban umum.</p>
            </a>

            <a href="{{ route('pelapor.status') }}"
               class="group bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md hover:border-brand-500 transition-all">
                <div class="text-3xl mb-3">🔎</div>
                <h3 class="font-headline font-bold text-lg text-slate-800 group-hover:text-brand-600">Cek Status Laporan</h3>
                <p class="mt-1 text-sm text-slate-500">Pantau perkembangan laporan Anda menggunakan nomor / kode pengaduan.</p>
            </a>

            <a href="{{ route('pelapor.feedback') }}"
               class="group bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md hover:border-brand-500 transition-all">
                <div class="text-3xl mb-3">⭐</div>
                <h3 class="font-headline font-bold text-lg text-slate-800 group-hover:text-brand-600">Kepuasan Masyarakat</h3>
                <p class="mt-1 text-sm text-slate-500">Beri penilaian terhadap layanan kami setelah laporan Anda selesai.</p>
            </a>
        </div>
    </section>

    {{-- Alur --}}
    <section class="bg-white border-y border-slate-200 py-12">
        <div class="text-center mb-8">
            <h2 class="font-headline text-2xl font-bold text-slate-800">Cara Melapor</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 text-center max-w-4xl mx-auto">
            <div>
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg">1</div>
                <h4 class="mt-3 font-semibold">Pilih Jenis Laporan</h4>
                <p class="text-sm text-slate-500 mt-1">Darurat atau Informasi &amp; Pengaduan</p>
            </div>
            <div>
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg">2</div>
                <h4 class="mt-3 font-semibold">Isi Detail &amp; Lokasi</h4>
                <p class="text-sm text-slate-500 mt-1">Sertakan foto/video bila ada</p>
            </div>
            <div>
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg">3</div>
                <h4 class="mt-3 font-semibold">Kirim &amp; Dapat Kode</h4>
                <p class="text-sm text-slate-500 mt-1">Simpan kode laporan Anda</p>
            </div>
            <div>
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg">4</div>
                <h4 class="mt-3 font-semibold">Pantau Status</h4>
                <p class="text-sm text-slate-500 mt-1">Cek perkembangan &amp; beri feedback</p>
            </div>
        </div>
    </section>

    {{-- Statistik publik --}}
    <section class="py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="flex flex-col items-center">
                <div class="text-4xl mb-2">📝</div>
                <div class="font-headline text-4xl font-bold text-brand-700">12,450</div>
                <div class="text-sm text-slate-500 uppercase tracking-wide mt-1">Total Laporan</div>
            </div>
            <div class="flex flex-col items-center">
                <div class="text-4xl mb-2">✅</div>
                <div class="font-headline text-4xl font-bold text-brand-700">98%</div>
                <div class="text-sm text-slate-500 uppercase tracking-wide mt-1">Tingkat Penyelesaian</div>
            </div>
            <div class="flex flex-col items-center">
                <div class="text-4xl mb-2">⏱️</div>
                <div class="font-headline text-4xl font-bold text-brand-700">&lt; 15 Min</div>
                <div class="text-sm text-slate-500 uppercase tracking-wide mt-1">Rata-rata Respon</div>
            </div>
        </div>
    </section>

    {{-- Darurat hotline --}}
    <section class="bg-red-600 text-white rounded-xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="bg-white/20 p-4 rounded-full">
                <div class="text-3xl">🚨</div>
            </div>
            <div>
                <h2 class="font-headline text-xl font-bold">Layanan Darurat Ketertiban Umum</h2>
                <p class="text-red-100">Tersedia 24 jam / 7 hari seminggu</p>
            </div>
        </div>
        <a href="{{ route('pelapor.lapor', ['jenis' => 'darurat']) }}" class="bg-white text-red-600 font-semibold px-8 py-3 rounded-lg hover:bg-red-50 transition-colors whitespace-nowrap">
            Lapor Sekarang
        </a>
    </section>
@endsection
