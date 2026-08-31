<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'SIPRAJA') | SIPRAJA</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eef4fb',
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
<body class="bg-slate-50 text-slate-800 font-sans flex flex-col min-h-screen">

    {{-- Top Navigation --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-1">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA" class="h-10 w-auto">
                <span class="font-headline font-bold text-xl text-brand-600">SIPRAJA</span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="{{ route('pelapor.lapor') }}" class="hover:text-brand-600">Buat Laporan</a>
                <a href="{{ route('pelapor.status') }}" class="hover:text-brand-600">Cek Status</a>
                <a href="{{ route('pelapor.feedback') }}" class="hover:text-brand-600">Feedback</a>
            </nav>

            <div class="md:hidden">
                <button id="nav-toggle" class="text-slate-700 p-2" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <nav id="mobile-nav" class="hidden md:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-3 text-sm font-medium text-slate-600">
            <a href="{{ route('pelapor.lapor') }}" class="block hover:text-brand-600">Buat Laporan</a>
            <a href="{{ route('pelapor.status') }}" class="block hover:text-brand-600">Cek Status</a>
            <a href="{{ route('pelapor.feedback') }}" class="block hover:text-brand-600">Feedback</a>
        </nav>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-800 text-slate-300">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
            <span>© {{ date('Y') }} SIPRAJA — Satuan Polisi Pamong Praja</span>
            <div class="flex gap-6">
                <a href="#" class="hover:text-white">Tentang</a>
                <a href="#" class="hover:text-white">Kontak</a>
                <a href="#" class="hover:text-white">Bantuan</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            document.getElementById('mobile-nav').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
