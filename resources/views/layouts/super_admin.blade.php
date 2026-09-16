<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f6f7f9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        'sidebar-active': '#2b4f7c',
                        'sidebar-hover': '#234a70',
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

            <a href="{{ route('super_admin.peta') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.peta') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">map</span>
                <span>Peta Persebaran</span>
            </a>

            <a href="{{ route('super_admin.monitoring') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('super_admin.monitoring') ? 'bg-super-active text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-super-hover hover:text-white' }}">
                <span class="material-symbols-outlined text-[20px]">monitoring</span>
                <span>Monitoring Sistem</span>
            </a>
        </nav>

        <!-- Bottom Action -->
        <div class="px-4 py-4 border-t border-white/10">
            <a href="{{ route('pelapor.index') }}"
                class="flex w-full items-center gap-3 px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali ke Portal</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f6f7f9]">
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <!-- Search Input -->
            <x-global-search />

            <!-- Header Action Icons -->
            <div class="flex items-center gap-2 relative">
                <!-- Notifikasi -->
                <div class="relative">
                    <button onclick="toggleNotif(event)" data-notification-button data-notifications-url="{{ route('notifications.index') }}" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors relative" title="Notifikasi">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        @if ($layoutUnreadNotificationCount > 0)<span data-notification-badge class="absolute right-1 top-1 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>@endif
                    </button>

                    <div id="notif-panel" class="hidden absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <h3 class="font-headline font-bold text-slate-800">Notifikasi</h3>
                            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-xs font-medium text-super-active hover:text-brand-600">Tandai semua dibaca</button></form>
                        </div>
                        <div data-notification-list class="max-h-96 divide-y divide-slate-50 overflow-y-auto">
                            @forelse ($layoutNotifications as $notification)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf
                                    <button class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-super-active/5' }}">
                                        <span class="material-symbols-outlined flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[18px] text-blue-600">notifications</span>
                                        <span class="min-w-0 flex-1"><span class="block text-sm font-medium text-slate-700">{{ data_get($notification->data, 'title', 'Notifikasi') }}</span><span class="block text-xs text-slate-500">{{ data_get($notification->data, 'message', '-') }}</span><span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span></span>
                                        @if (!$notification->read_at)<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-super-active"></span>@endif
                                    </button>
                                </form>
                            @empty
                                <div class="px-4 py-10 text-center text-slate-400"><div class="mb-2 text-3xl">🔔</div><p class="text-sm">Tidak ada notifikasi.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Avatar Profile / Topbar Profile Icon -->
                <a href="{{ route('super_admin.profile') }}">
                    <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                        <div class="w-9 h-9 rounded-full bg-super-active/15 text-super-active ring-2 ring-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <div class="hidden md:block text-left">
                            <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-500">Super Admin</div>
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

    <script>
        function toggleNotif(e) {
            e.stopPropagation();
            const panel = document.getElementById('notif-panel');
            panel.classList.toggle('hidden');
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

    </script>

    <!-- Global Modal Konfirmasi Hapus Data -->
    <div id="global-modal-konfirmasi-hapus" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop transparan tanpa blur -->
        <div class="fixed inset-0 bg-black/40 transition-opacity" onclick="tutupKonfirmasiHapus()"></div>

        <!-- Modal Box -->
        <div class="relative w-full max-w-sm sm:max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden p-6 text-center border border-slate-100 transform transition-all">
            <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">delete</span>
            </div>
            
            <h3 class="font-headline font-bold text-lg text-slate-800 mb-2" id="global-konfirmasi-judul">Konfirmasi Hapus Data</h3>
            <p class="text-sm text-slate-500 mb-6 leading-relaxed" id="global-konfirmasi-pesan">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
            
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
        let _confirmResolve = null;

        window.konfirmasiHapus = function(options) {
            return new Promise((resolve) => {
                let title = 'Konfirmasi Hapus Data';
                let message = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
                let confirmText = 'Ya, Hapus';
                let onConfirm = null;

                if (typeof options === 'function') {
                    onConfirm = options;
                } else if (typeof options === 'string') {
                    message = options;
                } else if (options && typeof options === 'object') {
                    if (options.title) title = options.title;
                    if (options.message) message = options.message;
                    if (options.confirmText) confirmText = options.confirmText;
                    if (options.onConfirm) onConfirm = options.onConfirm;
                }

                document.getElementById('global-konfirmasi-judul').textContent = title;
                document.getElementById('global-konfirmasi-pesan').textContent = message;

                _confirmResolve = resolve;

                const btnYa = document.getElementById('global-konfirmasi-btn-ya');
                btnYa.textContent = confirmText;
                btnYa.onclick = function() {
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

        window.tutupKonfirmasiHapus = function() {
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
        <div id="global-toast-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] transition-opacity duration-300 opacity-0" onclick="tutupToastPopup()"></div>

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
                <div id="global-toast-progress" class="h-full bg-emerald-500 rounded-full transition-all ease-linear" style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <script>
        let _toastTimer = null;
        let _toastProgressTimer = null;

        window.tutupToastPopup = function() {
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

        window.tampilToast = window.tampilPopup = function(msg, type = 'success', customTitle = null) {
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

        window.tampilkanToastLaluMuatUlang = function(message, type = 'success') {
            window.tampilToast(message, type);
            setTimeout(() => window.location.reload(), type === 'success' ? 900 : 1800);
        };

        document.addEventListener('submit', function(event) {
            const form = event.target.closest('form[data-confirm-delete]');

            if (!form || form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            window.konfirmasiHapus({
                title: 'Konfirmasi Hapus Data',
                message: form.dataset.confirmDelete,
                onConfirm: function() {
                    form.dataset.confirmed = 'true';
                    form.submit();
                },
            });
        });

        document.addEventListener('submit', function(event) {
            if (event.defaultPrevented) {
                return;
            }

            const form = event.target.closest('form');
            const method = (form?.querySelector('[name="_method"]')?.value || form?.method || 'GET').toUpperCase();

            if (!form || method === 'GET') {
                return;
            }

            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                button.disabled = true;
                button.classList.add('cursor-wait', 'opacity-70');
            });
        });
    </script>

    @if (session('status'))
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                window.tampilToast(@json(session('status')), 'success');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                window.tampilToast(@json(session('error')), 'error');
            });
        </script>
    @endif

    <script src="{{ asset('js/kulon-progo-map.js') }}"></script>
    <script src="{{ asset('js/header-live.js') }}"></script>
    @stack('scripts')
</body>
</html>
