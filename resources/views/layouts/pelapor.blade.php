<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SIPRAJA') | SIPRAJA</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef4fb',
                            100: '#d9e6f5',
                            500: '#2d5c8a',
                            600: '#234a70',
                            700: '#1c3a59',
                        },
                        accent: {
                            500: '#e8772e',
                            600: '#d3631c',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    @stack('styles')
</head>

<body class="bg-slate-100 text-slate-800 font-sans flex flex-col min-h-screen [overflow-x:clip]">

    {{-- Top Navigation --}}
    <header class="bg-slate-50 border-b border-slate-200 sticky top-0 z-20 shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-1">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA" class="h-10 w-auto">
                <span class="font-headline font-bold text-xl text-brand-600">SIPRAJA</span>
            </a>

            <div class="hidden md:flex items-center gap-6">
                <nav class="flex items-center gap-6 text-sm font-medium text-slate-600">
                    <a href="{{ route('pelapor.lapor') }}" class="hover:text-brand-600">Buat Laporan</a>
                    <a href="{{ route('pelapor.status') }}" class="hover:text-brand-600">Cek Status</a>
                    <a href="{{ route('pelapor.feedback') }}" class="hover:text-brand-600">Survey Kepuasan</a>
                </nav>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Masuk</span>
                </a>
            </div>

            <div class="md:hidden">
                <button id="nav-toggle" class="text-slate-700 p-2" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <nav id="mobile-nav" class="hidden md:hidden border-t border-slate-200 bg-slate-100 px-4 py-3 space-y-3 text-sm font-medium text-slate-600">
            <a href="{{ route('pelapor.lapor') }}" class="block hover:text-brand-600">Buat Laporan</a>
            <a href="{{ route('pelapor.status') }}" class="block hover:text-brand-600">Cek Status</a>
            <a href="{{ route('pelapor.feedback') }}" class="block hover:text-brand-600">Survey Kepuasan</a>
            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span>Masuk</span>
            </a>
        </nav>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-800 text-slate-300 border-t border-slate-700">
        <div class="max-w-6xl mx-auto px-4 py-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                {{-- Logo + Nama --}}
                <div class="flex items-start gap-4">
                    <img src="{{ asset('images/logo-kulonprogo.png') }}" alt="Logo Kulon Progo" class="h-20 w-auto flex-shrink-0">
                    <div>
                        <h3 class="font-headline font-bold text-white text-lg leading-tight">Satuan Polisi Pamong Praja</h3>
                        <p class="text-slate-400 font-medium">Kulon Progo</p>
                        <p class="mt-3 text-sm leading-relaxed">
                            Jl. Sugiman, Margosari, Pengasih,<br>
                            Kabupaten Kulon Progo,<br>
                            Daerah Istimewa Yogyakarta 55652
                        </p>
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="space-y-2.5 text-sm">
                    <p>Telp. 0274 2890234 – Fax. 0</p>
                    <p>Email: <a href="mailto:polpp@kulonprogokab.go.id" class="hover:text-white">polpp@kulonprogokab.go.id</a></p>
                    <p>Call Center : 0</p>
                    <div class="pt-2 flex flex-wrap gap-6">
                        <a href="#" class="hover:text-white">Tentang</a>
                        <a href="#" class="hover:text-white">Kontak</a>
                        <a href="#" class="hover:text-white">Bantuan</a>
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-700 text-center text-sm text-slate-400">
                © 2026 SIPRAJA — Satuan Polisi Pamong Praja
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function() {
            document.getElementById('mobile-nav').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>

</html>
