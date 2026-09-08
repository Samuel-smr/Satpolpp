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
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA Satpol PP"
                    class="w-full h-full object-contain">
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

            <a href="{{ route('admin.evaluasi') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.evaluasi') ? 'bg-sidebar-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">assessment</span>
                <span>Evaluasi Kegiatan</span>
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
            <div class="flex items-center gap-2 relative">
                <!-- Notifikasi -->
                <div class="relative">
                    <button onclick="toggleNotif(event)"
                        class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors relative"
                        title="Notifikasi">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span id="notif-badge"
                            class="hidden absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>

                    <div id="notif-panel"
                        class="hidden absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <h3 class="font-headline font-bold text-slate-800">Notifikasi</h3>
                            <button onclick="markAllRead()"
                                class="text-xs font-medium text-super-active hover:text-brand-600">Tandai semua
                                dibaca</button>
                        </div>
                        <div id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-50"></div>
                        <div id="notif-empty" class="hidden px-4 py-10 text-center text-slate-400">
                            <div class="text-3xl mb-2">🔔</div>
                            <p class="text-sm">Tidak ada notifikasi.</p>
                        </div>
                    </div>
                </div>

                <button
                    class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors"
                    title="Bantuan">
                    <span class="material-symbols-outlined text-[20px]">help_outline</span>
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

    <!-- Global Modal Konfirmasi Hapus Data -->
    <div id="global-modal-konfirmasi-hapus" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop transparan tanpa blur -->
        <div class="fixed inset-0 bg-black/40 transition-opacity" onclick="tutupKonfirmasiHapus()"></div>

        <!-- Modal Box -->
        <div
            class="relative w-full max-w-sm sm:max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden p-6 text-center border border-slate-100 transform transition-all">
            <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">delete</span>
            </div>

            <h3 class="font-headline font-bold text-lg text-slate-800 mb-2" id="global-konfirmasi-judul">Konfirmasi
                Hapus Data</h3>
            <p class="text-sm text-slate-500 mb-6 leading-relaxed" id="global-konfirmasi-pesan">Apakah Anda yakin ingin
                menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>

            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="tutupKonfirmasiHapus()"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="button" id="global-konfirmasi-btn-ya"
                    class="w-full py-2.5 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-sm font-semibold text-white shadow-sm transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <script>
        const NOTIF_KODE_LAPORAN = 'sipraja_reports';
        const NOTIF_KODE_BACA = 'sipraja_notif_baca';

        function getReportsData() {
            try {
                return JSON.parse(localStorage.getItem(NOTIF_KODE_LAPORAN) || '[]');
            } catch (e) {
                return [];
            }
        }

        function getBacaSet() {
            try {
                return new Set(JSON.parse(localStorage.getItem(NOTIF_KODE_BACA) || '[]'));
            } catch (e) {
                return new Set();
            }
        }

        function simpanBacaSet(set) {
            localStorage.setItem(NOTIF_KODE_BACA, JSON.stringify([...set]));
        }

        function getNotif() {
            return getReportsData().map((r, i) => {
                const darurat = r.jenis === 'darurat';
                return {
                    id: i,
                    kode: r.kode,
                    jenis: darurat ? 'darurat' : 'laporan',
                    pesan: (darurat ? 'Laporan Darurat: ' : 'Laporan Baru: ') + (r.deskripsi || 'Pengaduan baru dari masyarakat.'),
                    waktu: r.dibuat || '-',
                    dibaca: getBacaSet().has(r.kode),
                };
            });
        }

        function notifIcon(j) {
            return { darurat: 'emergency', laporan: 'description', petugas: 'badge', aset: 'inventory_2', organisasi: 'account_balance', evaluasi: 'assessment' }[j] || 'notifications';
        }
        function notifClass(j) {
            return { darurat: 'bg-red-100 text-red-600', laporan: 'bg-blue-100 text-blue-600', petugas: 'bg-emerald-100 text-emerald-600', aset: 'bg-amber-100 text-amber-600', organisasi: 'bg-slate-200 text-slate-600', evaluasi: 'bg-purple-100 text-purple-600' }[j] || 'bg-slate-100 text-slate-600';
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
                    <div class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors ${n.dibaca ? '' : 'bg-orange-50/60'}" onclick="readNotif('${n.kode}')">
                        <div class="w-9 h-9 rounded-full ${notifClass(n.jenis)} flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[18px]">${notifIcon(n.jenis)}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-snug">${n.pesan}</p>
                            <span class="text-xs text-slate-400">${n.waktu}</span>
                        </div>
                        ${!n.dibaca ? '<span class="w-2 h-2 mt-1.5 bg-red-500 rounded-full flex-shrink-0"></span>' : ''}
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

        function readNotif(kode) {
            if (kode) {
                const set = getBacaSet();
                set.add(kode);
                simpanBacaSet(set);
                renderNotif();
                window.location.href = "{{ url('admin/pengaduan') }}/" + encodeURIComponent(kode);
            }
        }

        function markAllRead() {
            const set = new Set(getReportsData().map(r => r.kode));
            simpanBacaSet(set);
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

        let _confirmResolve = null;

        window.konfirmasiHapus = function (options) {
            return new Promise((resolve) => {
                let title = 'Konfirmasi Hapus Data';
                let message = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
                let onConfirm = null;

                if (typeof options === 'function') {
                    onConfirm = options;
                } else if (typeof options === 'string') {
                    message = options;
                } else if (options && typeof options === 'object') {
                    if (options.title) title = options.title;
                    if (options.message) message = options.message;
                    if (options.onConfirm) onConfirm = options.onConfirm;
                }

                document.getElementById('global-konfirmasi-judul').textContent = title;
                document.getElementById('global-konfirmasi-pesan').textContent = message;

                _confirmResolve = resolve;

                const btnYa = document.getElementById('global-konfirmasi-btn-ya');
                btnYa.onclick = function () {
                    tutupKonfirmasiHapus();
                    if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                    resolve(true);
                };

                const modal = document.getElementById('global-modal-konfirmasi-hapus');
                if (modal) modal.classList.remove('hidden');
            });
        };

        window.tutupKonfirmasiHapus = function () {
            const modal = document.getElementById('global-modal-konfirmasi-hapus');
            if (modal) modal.classList.add('hidden');
            if (_confirmResolve) {
                _confirmResolve(false);
                _confirmResolve = null;
            }
        };
    </script>

    {{-- Global Success / Alert Popup Modal --}}
    <div id="global-toast-popup" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="global-toast-backdrop"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] transition-opacity duration-300 opacity-0"
            onclick="tutupToastPopup()"></div>

        <!-- Popup Card Box -->
        <div id="global-toast-box"
            class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-7 text-center transform transition-all duration-300 scale-90 opacity-0 z-10">
            {{-- Icon Badge --}}
            <div id="global-toast-icon-wrap"
                class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-emerald-50 transition-colors duration-300">
                <span id="global-toast-icon" class="material-symbols-outlined text-[36px]">check_circle</span>
            </div>
            {{-- Title --}}
            <h3 id="global-toast-title" class="font-headline font-bold text-xl text-slate-800 mb-1.5">Berhasil!</h3>
            {{-- Message --}}
            <p id="global-toast-message" class="text-sm text-slate-500 leading-relaxed mb-6 font-normal"></p>
            {{-- Action Button --}}
            <button type="button" onclick="tutupToastPopup()" id="global-toast-btn"
                class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                Selesai
            </button>
            {{-- Progress bar --}}
            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden mt-4">
                <div id="global-toast-progress" class="h-full bg-emerald-500 rounded-full transition-all ease-linear"
                    style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <script>
        let _toastTimer = null;
        let _toastProgressTimer = null;

        window.tutupToastPopup = function () {
            clearTimeout(_toastTimer);
            clearInterval(_toastProgressTimer);
            const popup = document.getElementById('global-toast-popup');
            const box = document.getElementById('global-toast-box');
            const backdrop = document.getElementById('global-toast-backdrop');
            if (!popup || !box) return;

            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-90', 'opacity-0');
            if (backdrop) {
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
            }

            setTimeout(() => {
                popup.classList.add('hidden');
            }, 250);
        };

        window.tampilToast = window.tampilPopup = function (msg, type = 'success', customTitle = null) {
            clearTimeout(_toastTimer);
            clearInterval(_toastProgressTimer);

            const popup = document.getElementById('global-toast-popup');
            const box = document.getElementById('global-toast-box');
            const backdrop = document.getElementById('global-toast-backdrop');
            const iconWrap = document.getElementById('global-toast-icon-wrap');
            const icon = document.getElementById('global-toast-icon');
            const title = document.getElementById('global-toast-title');
            const message = document.getElementById('global-toast-message');
            const btn = document.getElementById('global-toast-btn');
            const progress = document.getElementById('global-toast-progress');

            if (!popup || !box) return;

            const config = {
                success: {
                    title: customTitle || 'Berhasil!',
                    icon: 'check_circle',
                    wrap: 'bg-emerald-100 text-emerald-600 ring-emerald-50',
                    btn: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20',
                    bar: 'bg-emerald-500'
                },
                error: {
                    title: customTitle || 'Gagal!',
                    icon: 'error',
                    wrap: 'bg-red-100 text-red-600 ring-red-50',
                    btn: 'bg-red-600 hover:bg-red-700 shadow-red-600/20',
                    bar: 'bg-red-500'
                },
                info: {
                    title: customTitle || 'Informasi',
                    icon: 'info',
                    wrap: 'bg-blue-100 text-blue-600 ring-blue-50',
                    btn: 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
                    bar: 'bg-blue-500'
                },
                warning: {
                    title: customTitle || 'Perhatian',
                    icon: 'warning',
                    wrap: 'bg-amber-100 text-amber-600 ring-amber-50',
                    btn: 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20',
                    bar: 'bg-amber-500'
                },
            };
            const c = config[type] || config.success;

            icon.textContent = c.icon;
            title.textContent = c.title;
            message.textContent = msg || '';
            iconWrap.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ring-8 transition-colors duration-300 ' + c.wrap;
            btn.className = 'w-full py-2.5 px-4 rounded-xl active:scale-[0.98] text-sm font-semibold text-white shadow-md transition-all cursor-pointer ' + c.btn;
            progress.className = 'h-full rounded-full transition-all ease-linear ' + c.bar;
            progress.style.width = '100%';

            // Show modal & trigger transitions
            popup.classList.remove('hidden');
            requestAnimationFrame(() => {
                setTimeout(() => {
                    box.classList.remove('scale-90', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.add('opacity-100');
                    }
                }, 10);
            });

            // Countdown progress bar & auto close after 3 seconds
            const duration = 3000;
            const interval = 30;
            let elapsed = 0;
            _toastProgressTimer = setInterval(() => {
                elapsed += interval;
                const pct = Math.max(0, 100 - (elapsed / duration) * 100);
                progress.style.width = pct + '%';
                if (elapsed >= duration) clearInterval(_toastProgressTimer);
            }, interval);

            _toastTimer = setTimeout(() => {
                window.tutupToastPopup();
            }, duration);
        };
    </script>

    <script src="{{ asset('js/kulon-progo-map.js') }}"></script>
    @stack('scripts')
</body>

</html>
