@extends('layouts.super_admin')

@section('title', 'Monitoring Sistem')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Status &amp; Monitoring Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Status server, performa, dan log aktivitas SIPRAJA.</p>
        </div>

        <button type="button" onclick="simulasiRefresh()"
            class="inline-flex items-center justify-center gap-2 bg-super-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">refresh</span>
            Refresh Status
        </button>
    </div>

    {{-- Status Layanan --}}
    <div class="mb-8">
        <h2 class="font-headline text-lg font-bold text-slate-800 mb-4">Status Layanan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">dns</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Server Utama</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-800">Operasional</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Uptime 99.98%</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">database</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Database</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-800">Normal</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Lantensi 12 ms</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">code</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">API / Backend</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-800">Responsif</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Respon 230 ms</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">storage</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Storage</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-slate-800">62% Terpakai</span>
                    </div>
                    <div class="w-24 h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width:62%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Performa --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-headline font-bold text-slate-800">Permintaan Sistem</h3>
                    <p class="text-xs text-slate-400">Total permintaan per minggu</p>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-slate-400 font-medium">Zona WIB</span>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="chart-request"></canvas>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-headline font-bold text-slate-800">Pengguna Aktif</h3>
                    <p class="text-xs text-slate-400">Sesi aktif harian (30 hari)</p>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="chart-users"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Metrik Real-Time --}}
        <div class="lg:col-span-1 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <h3 class="font-headline font-bold text-slate-800 mb-4">Metrik Real-time</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">CPU Server</span>
                        <span class="font-semibold text-slate-800">34%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full" style="width:34%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Memori (RAM)</span>
                        <span class="font-semibold text-slate-800">58%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-super-active rounded-full" style="width:58%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Bandwidth</span>
                        <span class="font-semibold text-slate-800">41%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width:41%"></div>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Terakhir diperbarui</p>
                    <p class="text-sm text-slate-600" id="last-update">--</p>
                </div>
            </div>
        </div>

        {{-- Log Aktivitas --}}
        <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="font-headline font-bold text-slate-800">Log Aktivitas Sistem</h3>
                    <p class="text-xs text-slate-400">Audit trail terbaru</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy</span>
            </div>
            <div class="divide-y divide-slate-50 max-h-[340px] overflow-y-auto" id="log-list"></div>
        </div>
    </div>



@endsection

@push('scripts')
<script>
    const KODE = 'sipraja_log';

    function getLogs() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { waktu: '09:42:15', aksi: 'Super Admin login ke sistem', user: 'Dedi Kurniawan', jenis: 'auth' },
                { waktu: '09:38:02', aksi: 'Menambahkan petugas baru: Bambang Sutrisno', user: 'Dedi Kurniawan', jenis: 'petugas' },
                { waktu: '09:30:47', aksi: 'Menandai laporan SPJ-584021 sebagai selesai', user: 'Admin Satpol PP', jenis: 'laporan' },
                { waktu: '09:21:30', aksi: 'Menambahkan aset: Mobil Patroli Dalmas', user: 'Admin Satpol PP', jenis: 'aset' },
                { waktu: '09:12:05', aksi: 'Mengubah status petugas Agus Riyanto menjadi cuti', user: 'Dedi Kurniawan', jenis: 'petugas' },
                { waktu: '09:02:11', aksi: 'Memperbarui data unit kerja', user: 'Dedi Kurniawan', jenis: 'organisasi' },
                { waktu: '08:55:43', aksi: 'Laporan baru diterima dari masyarakat (SPJ-584100)', user: 'Sistem', jenis: 'laporan' },
                { waktu: '08:47:19', aksi: 'Login berhasil: Admin Satpol PP', user: 'Admin Satpol PP', jenis: 'auth' },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function jenisClass(j) {
        if (j === 'auth') return 'bg-purple-100 text-purple-700';
        if (j === 'laporan') return 'bg-blue-100 text-blue-700';
        if (j === 'aset') return 'bg-amber-100 text-amber-700';
        if (j === 'organisasi') return 'bg-slate-200 text-slate-700';
        return 'bg-emerald-100 text-emerald-700';
    }

    function iconJenis(j) {
        if (j === 'auth') return 'lock';
        if (j === 'laporan') return 'description';
        if (j === 'aset') return 'inventory_2';
        if (j === 'organisasi') return 'account_balance';
        return 'badge';
    }

    function renderLogs() {
        const logs = getLogs();
        document.getElementById('log-list').innerHTML = logs.map(l => `
            <div class="flex items-center gap-3 px-6 py-3">
                <div class="w-8 h-8 rounded-full ${jenisClass(l.jenis)} flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">${iconJenis(l.jenis)}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-700 truncate">${l.aksi}</p>
                    <p class="text-xs text-slate-400">oleh ${l.user}</p>
                </div>
                <span class="text-xs text-slate-400 font-mono flex-shrink-0">${l.waktu}</span>
            </div>
        `).join('');
    }

    function simulasiRefresh() {
        document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const t = document.getElementById('toast');
        t.textContent = 'Status sistem diperbarui pada ' + document.getElementById('last-update').textContent;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2500);
    }

    // Grafik performa
    function initCharts() {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';

        new Chart(document.getElementById('chart-request'), {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Permintaan',
                    data: [1200, 1450, 1380, 1620, 1550, 980, 720],
                    borderColor: '#234a70',
                    backgroundColor: 'rgba(35,74,112,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        new Chart(document.getElementById('chart-users'), {
            type: 'bar',
            data: {
                labels: ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7'],
                datasets: [{
                    label: 'Pengguna Aktif',
                    data: [210, 260, 240, 300, 285, 180, 150],
                    backgroundColor: '#2b4f7c',
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });
    }

    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    renderLogs();
    initCharts();
</script>
@endpush
