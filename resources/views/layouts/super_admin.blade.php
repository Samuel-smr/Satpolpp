<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f6f7f9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Super Admin Dashboard') | SIPRAJA Satpol PP</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS & Chart.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'super-sidebar': '#240d05',
                        'super-active': '#2b4f7c',
                        'super-hover': '#33170c',
                        brand: {
                            50:  '#eef4fb',
                            100: '#d9e6f5',
                            500: '#2d5c8a',
                            600: '#234a70',
                            700: '#1c3a59',
                        },
                        accent: {
                            500: '#fd8b00',
                            600: '#e57a00',
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
    <aside class="w-64 bg-super-sidebar text-slate-200 flex flex-col flex-shrink-0 z-30 transition-all duration-300">
        <!-- Logo Section -->
        <div class="px-6 py-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center p-1.5 flex-shrink-0">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA Satpol PP" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-headline font-bold text-lg text-white leading-tight tracking-wide">SIPRAJA</h1>
                <p class="text-[11px] text-slate-400 font-medium">Satpol PP Super Admin</p>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-3 space-y-1.5 overflow-y-auto">
            <a href="{{ route('super_admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.dashboard') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">grid_view</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('super_admin.kegiatan') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.kegiatan') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">event</span>
                <span>Kegiatan</span>
            </a>

            <a href="{{ route('super_admin.user') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.user') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">group</span>
                <span>Manajemen User</span>
            </a>

            <a href="{{ route('super_admin.organisasi') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.organisasi') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">account_balance</span>
                <span>Manajemen Organisasi</span>
            </a>

            <a href="{{ route('super_admin.petugas') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.petugas') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">badge</span>
                <span>Manajemen Petugas</span>
            </a>

            <a href="{{ route('super_admin.evaluasi') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.evaluasi') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">assessment</span>
                <span>Evaluasi Kegiatan</span>
            </a>

            <a href="{{ route('super_admin.monitoring') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.monitoring') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">monitoring</span>
                <span>Monitoring Sistem</span>
            </a>
        </nav>

        <!-- Bottom Actions (Settings & Logout) -->
        <div class="px-4 py-4 border-t border-white/10">
            <div class="space-y-1">
                <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">settings</span>
                    <span>Settings</span>
                </a>
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    {{-- Main Area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f6f7f9]">
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-3.5 flex items-center justify-end sticky top-0 z-20 gap-4">
            <!-- Search Bar -->
            <div class="w-72">
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-[18px]">search</span>
                    <input type="text"
                           placeholder="Search..."
                           class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-full text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all" />
                </div>
            </div>

            <!-- Header Action Icons -->
            <div class="flex items-center gap-2 relative">
                <!-- Notifikasi -->
                <div class="relative">
                    <button onclick="toggleNotif(event)" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors relative" title="Notifikasi">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span id="notif-badge" class="hidden absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>

                    <div id="notif-panel" class="hidden absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <h3 class="font-headline font-bold text-slate-800">Notifikasi</h3>
                            <button onclick="markAllRead()" class="text-xs font-medium text-super-active hover:text-brand-600">Tandai semua dibaca</button>
                        </div>
                        <div id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-50"></div>
                        <div id="notif-empty" class="hidden px-4 py-10 text-center text-slate-400">
                            <div class="text-3xl mb-2">🔔</div>
                            <p class="text-sm">Tidak ada notifikasi.</p>
                        </div>
                    </div>
                </div>

                <button class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors" title="Bantuan">
                    <span class="material-symbols-outlined text-[20px]">help_outline</span>
                </button>

                <!-- Profile Avatar -->
                <div class="pl-2 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-200 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150" alt="Super Admin Avatar" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        const NOTIF_KODE = 'sipraja_notifikasi';

        function getNotif() {
            let data = JSON.parse(localStorage.getItem(NOTIF_KODE) || '[]');
            if (data.length === 0) {
                data = [
                    { id: 1, jenis: 'laporan', pesan: 'Laporan baru diterima dari masyarakat (SPJ-584100).', waktu: 'Baru saja', dibaca: false },
                    { id: 2, jenis: 'petugas', pesan: 'Petugas Bambang Sutrisno ditambahkan ke Regu A.', waktu: '10 menit lalu', dibaca: false },
                    { id: 3, jenis: 'aset', pesan: 'Aset baru ditambahkan: Mobil Patroli Dalmas.', waktu: '1 jam lalu', dibaca: false },
                    { id: 4, jenis: 'organisasi', pesan: 'Data unit kerja diperbarui oleh Admin.', waktu: '3 jam lalu', dibaca: true },
                    { id: 5, jenis: 'evaluasi', pesan: 'Evaluasi kegiatan bulan ini telah diperbarui.', waktu: '1 hari lalu', dibaca: true },
                ];
                localStorage.setItem(NOTIF_KODE, JSON.stringify(data));
            }
            return data;
        }

        function saveNotif(data) { localStorage.setItem(NOTIF_KODE, JSON.stringify(data)); }

        function notifIcon(j) {
            return { laporan: 'description', petugas: 'badge', aset: 'inventory_2', organisasi: 'account_balance', evaluasi: 'assessment' }[j] || 'notifications';
        }
        function notifClass(j) {
            return { laporan: 'bg-blue-100 text-blue-600', petugas: 'bg-emerald-100 text-emerald-600', aset: 'bg-amber-100 text-amber-600', organisasi: 'bg-slate-200 text-slate-600', evaluasi: 'bg-purple-100 text-purple-600' }[j] || 'bg-slate-100 text-slate-600';
        }

        function unreadCount() {
            return getNotif().filter(n => !n.dibaca).length;
        }

        function updateBadge() {
            const badge = document.getElementById('notif-badge');
            const count = unreadCount();
            badge.classList.toggle('hidden', count === 0);
        }

        function renderNotif() {
            const data = getNotif();
            const list = document.getElementById('notif-list');
            const empty = document.getElementById('notif-empty');

            if (data.length === 0) {
                list.innerHTML = '';
                empty.classList.remove('hidden');
            } else {
                empty.classList.add('hidden');
                list.innerHTML = data.map(n => `
                    <div class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors ${n.dibaca ? '' : 'bg-super-active/5'}" onclick="readNotif(${n.id})">
                        <div class="w-9 h-9 rounded-full ${notifClass(n.jenis)} flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[18px]">${notifIcon(n.jenis)}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-snug">${n.pesan}</p>
                            <span class="text-xs text-slate-400">${n.waktu}</span>
                        </div>
                        ${!n.dibaca ? '<span class="w-2 h-2 mt-1.5 bg-super-active rounded-full flex-shrink-0"></span>' : ''}
                    </div>
                `).join('');
            }
            updateBadge();
        }

        function toggleNotif(e) {
            e.stopPropagation();
            const panel = document.getElementById('notif-panel');
            panel.classList.toggle('hidden');
            renderNotif();
        }

        function readNotif(id) {
            const data = getNotif();
            const n = data.find(x => x.id === id);
            if (n) n.dibaca = true;
            saveNotif(data);
            renderNotif();
        }

        function markAllRead() {
            const data = getNotif();
            data.forEach(n => n.dibaca = true);
            saveNotif(data);
            renderNotif();
        }

        // Tutup panel saat klik di luar
        document.addEventListener('click', function (e) {
            const panel = document.getElementById('notif-panel');
            if (panel && !panel.classList.contains('hidden')) {
                const btn = document.querySelector('[onclick="toggleNotif(event)"]');
                if (btn && !btn.contains(e.target) && !panel.contains(e.target)) {
                    panel.classList.add('hidden');
                }
            }
        });

        renderNotif();
    </script>
</body>
</html>
