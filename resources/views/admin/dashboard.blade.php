@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Dashboard Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan operasional dan status pengaduan warga.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Unduh Laporan</span>
            </button>
            <button type="button" class="px-4 py-2.5 bg-accent-600 hover:bg-accent-500 rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>+ Buat Tugas</span>
            </button>
        </div>
    </div>

    <!-- Summary Metric Cards (4 Cards Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Urgent -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">Pengaduan Urgent Hari Ini</p>
                    <h2 class="font-headline text-3xl font-bold text-red-600 mt-3">12</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100/70 text-red-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">warning</span>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <span class="text-xs font-medium text-red-600 bg-red-50 border border-red-100 rounded-full px-2 py-0.5 inline-flex items-center gap-1">
                    <span>📈</span> +2 dr kmrn
                </span>
            </div>
        </div>

        <!-- Card 2: Belum Ditindaklanjuti -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">Belum Ditindaklanjuti</p>
                    <h2 class="font-headline text-3xl font-bold text-amber-600 mt-3">34</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100/70 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">assignment_late</span>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <span class="text-xs font-medium text-slate-400">Butuh atensi</span>
            </div>
        </div>

        <!-- Card 3: Petugas Aktif -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">Petugas Aktif</p>
                    <h2 class="font-headline text-3xl font-bold text-slate-900 mt-3">128</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-brand-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">shield</span>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <span class="text-xs font-medium text-slate-400">Di lapangan</span>
            </div>
        </div>

        <!-- Card 4: Kegiatan Minggu Ini -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">Kegiatan Minggu Ini</p>
                    <h2 class="font-headline text-3xl font-bold text-slate-900 mt-3">45</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <span class="text-xs font-medium text-slate-500">➔ Stabil</span>
            </div>
        </div>
    </div>

    <!-- Main Content Section: Chart & Quick Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 8 cols: Statistik Pengaduan Mingguan Chart -->
        <div class="lg:col-span-8 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-headline text-xl font-bold text-slate-900">Statistik Pengaduan Mingguan</h3>
                </div>
                <div class="flex items-center gap-4">
                    <button type="button" class="text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg px-3 py-1.5 flex items-center gap-1.5 transition-colors">
                        <span>7 Hari Terakhir</span>
                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                    </button>
                </div>
            </div>

            <!-- Custom Legend -->
            <div class="flex items-center justify-end gap-6 mb-4 text-xs font-medium">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm bg-[#000a1e]"></span>
                    <span class="text-slate-600">Laporan Masuk</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full border-2 border-[#fd8b00] bg-white"></span>
                    <span class="text-slate-600">Diselesaikan</span>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="relative w-full h-[320px]">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <!-- Right 4 cols: Tugas Cepat -->
        <div class="lg:col-span-4 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-headline text-xl font-bold text-slate-900">Tugas Cepat</h3>
                    <a href="#" class="text-xs font-bold text-amber-700 hover:underline">Lihat Semua</a>
                </div>

                <!-- Task List -->
                <div class="space-y-4">
                    <!-- Task Item 1 -->
                    <div class="bg-white border border-slate-200 rounded-xl p-4 border-l-4 border-l-red-500 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-sm text-slate-800 truncate">Validasi Laporan Pungli #4092</h4>
                                <p class="text-xs text-slate-500 mt-1 truncate">Kec. Kebayoran Baru, dekat...</p>
                            </div>
                            <span class="text-[11px] font-semibold text-red-600 bg-red-50 border border-red-100 px-2.5 py-0.5 rounded-full flex-shrink-0">
                                Urgent
                            </span>
                        </div>
                    </div>

                    <!-- Task Item 2 -->
                    <div class="bg-white border border-slate-200 rounded-xl p-4 border-l-4 border-l-amber-500 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-sm text-slate-800 truncate">Assign Patroli Malam</h4>
                                <p class="text-xs text-slate-500 mt-1 truncate">Area Sudirman-Thamrin butuh...</p>
                            </div>
                            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-100 px-2.5 py-0.5 rounded-full flex-shrink-0">
                                Pending
                            </span>
                        </div>
                    </div>

                    <!-- Task Item 3 -->
                    <div class="bg-white border border-slate-200 rounded-xl p-4 border-l-4 border-l-slate-400 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-sm text-slate-800 truncate">Review Draft SOP Penertiban</h4>
                                <p class="text-xs text-slate-500 mt-1 truncate">Draft dari divisi hukum, due...</p>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-full flex-shrink-0">
                                Rutin
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Button -->
            <div class="mt-6">
                <a href="#" class="w-full flex items-center justify-center py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Buka Dashboard Task
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('weeklyChart').getContext('2d');

        const labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Diselesaikan',
                        data: [45, 48, 40, 56, 50, 18, 16],
                        borderColor: '#fd8b00',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#fd8b00',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        tension: 0.35,
                        order: 1
                    },
                    {
                        type: 'bar',
                        label: 'Laporan Masuk',
                        data: [45, 52, 38, 64, 48, 25, 20],
                        backgroundColor: '#000a1e',
                        borderRadius: 4,
                        barThickness: 34,
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { family: 'Inter', size: 12 },
                            color: '#64748b'
                        }
                    },
                    y: {
                        min: 0,
                        max: 70,
                        ticks: {
                            stepSize: 10,
                            font: { family: 'Inter', size: 12 },
                            color: '#64748b'
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
