<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f4f5f7]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') | SIPRAJA Satpol PP</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    {{-- Tailwind CSS & Chart.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: '#222718',
                        'sidebar-active': '#aa4d00',
                        'sidebar-hover': '#2d3321',
                        brand: {
                            50: '#eef4fb',
                            100: '#d9e6f5',
                            500: '#2d5c8a',
                            600: '#234a70',
                            700: '#1c3a59',
                        },
                        accent: {
                            500: '#d96a00',
                            600: '#aa4d00',
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

<body class="h-full font-sans antialiased text-slate-800 flex overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-sidebar text-slate-200 flex flex-col flex-shrink-0 z-30 transition-all duration-300">
        <!-- Logo Section -->
        <div class="px-6 py-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center p-1.5 flex-shrink-0">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA Satpol PP" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-headline font-bold text-lg text-white leading-tight tracking-wide">SIPRAJA</h1>
                <p class="text-xs text-slate-400 font-medium">Satpol PP Admin</p>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">trending_up</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.pengaduan') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.pengaduan') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">campaign</span>
                <span>Pengaduan</span>
            </a>

            <a href="{{ route('admin.kegiatan') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.kegiatan') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                <span>Acara &amp; Kegiatan</span>
            </a>

            <a href="{{ route('admin.feedback') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.feedback') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">chat_bubble_outline</span>
                <span>Feedback Masyarakat</span>
            </a>

            <a href="{{ route('admin.arsip') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.arsip') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">folder_open</span>
                <span>Arsip</span>
            </a>

            <a href="{{ route('admin.aset') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.aset') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                <span>Aset</span>
            </a>

            <a href="{{ route('admin.peta') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.peta') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">map</span>
                <span>Peta Persebaran</span>
            </a>
        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-white/10">
            <a href="{{ route('pelapor.index') }}"
                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali ke Portal</span>
            </a>
        </div>
    </aside>

    {{-- Main Area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f4f5f7]">
        <!-- Top Header Bar -->
        <header
            class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <!-- Search Input -->
            <div class="w-full max-w-md">
                <div class="relative">
                    <span
                        class="material-symbols-outlined text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px]">search</span>
                    <input type="text" placeholder="Cari laporan, petugas, atau kegiatan..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all" />
                </div>
            </div>

            <!-- Right Actions / Profile -->
            <div class="flex items-center gap-4">
                <button
                    class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors"
                    title="Pengaturan">
                    <span class="material-symbols-outlined text-[22px]">settings</span>
                </button>

                <button
                    class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors"
                    title="Bantuan">
                    <span class="material-symbols-outlined text-[22px]">help_outline</span>
                </button>

                <!-- Avatar Profile -->
                <!-- Topbar Profile Icon -->
                <a href="{{ route('admin.profile') }}">
                    <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-slate-700 overflow-hidden ring-2 ring-slate-100">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=150"
                                alt="Avatar Admin" class="w-full h-full object-cover">
                        </div>
                        <div class="hidden md:block text-left">
                            <div class="text-xs font-bold text-slate-800 leading-tight">Budi Santoso</div>
                            <div class="text-[10px] text-slate-500">Kasubag Operasional</div>
                        </div>
                    </div>
                </a>
            </div>
        </header>

        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>