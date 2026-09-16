@extends('layouts.super_admin')

@section('title', 'Monitoring Sistem')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Status &amp; Monitoring Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Status server dan performa SIPRAJA.</p>
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
                    <p class="text-xs text-slate-400 mt-0.5">Aplikasi dapat diakses</p>
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
                    <p class="text-xs text-slate-400 mt-0.5">Koneksi berhasil</p>
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
                    <p class="text-xs text-slate-400 mt-0.5">Laravel aktif</p>
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
                        <span class="font-semibold text-slate-800">Tersedia</span>
                    </div>
                    <div class="w-24 h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width:100%"></div>
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
                    <h3 class="font-headline font-bold text-slate-800">Aktivitas Sistem</h3>
                    <p class="text-xs text-slate-400">Audit log tujuh hari terakhir</p>
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
                    <h3 class="font-headline font-bold text-slate-800">Pelaku Aktivitas</h3>
                    <p class="text-xs text-slate-400">Pengguna unik pada audit log harian</p>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="chart-users"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        {{-- Metrik Real-Time --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <h3 class="font-headline font-bold text-slate-800 mb-4">Ringkasan Audit Hari Ini</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Aktivitas tercatat</span>
                        <span class="font-semibold text-slate-800">{{ $monitoringStats['today'] }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full" style="width:100%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Pengguna tercatat</span>
                        <span class="font-semibold text-slate-800">{{ $monitoringStats['actors'] }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-super-active rounded-full" style="width:100%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Modul terpantau</span>
                        <span class="font-semibold text-slate-800">{{ $monitoringStats['modules'] }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width:100%"></div>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Terakhir diperbarui</p>
                    <p class="text-sm text-slate-600" id="last-update">--</p>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
<script>
    const monitoringChart = @json($chart);

    function simulasiRefresh() {
        document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        window.location.reload();
    }

    // Grafik performa
    function initCharts() {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';

        new Chart(document.getElementById('chart-request'), {
            type: 'line',
            data: {
                labels: monitoringChart.map(item => item.label),
                datasets: [{
                    label: 'Permintaan',
                    data: monitoringChart.map(item => item.activities),
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
                labels: monitoringChart.map(item => item.label),
                datasets: [{
                    label: 'Pengguna Aktif',
                    data: monitoringChart.map(item => item.actors),
                    backgroundColor: '#2b4f7c',
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });
    }

    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    initCharts();
</script>
@endpush
