@extends('layouts.super_admin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Super Admin Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Overview of system operations and personnel statistics.</p>
    </div>

    <!-- Ringkasan Sistem Label -->
    <div class="mb-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Sistem</h2>
    </div>

    <!-- 4 Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Total User -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">group</span>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                    +2.4%
                </span>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">1,240</h3>
                <p class="text-xs font-medium text-slate-400 mt-1">Total User</p>
            </div>
        </div>

        <!-- Card 2: Total Petugas Aktif -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">badge</span>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                    Active
                </span>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">450</h3>
                <p class="text-xs font-medium text-slate-400 mt-1">Total Petugas Aktif</p>
            </div>
        </div>

        <!-- Card 3: Total Pengaduan Bulan Ini -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">error</span>
                </div>
                <span class="text-xs font-semibold text-red-600 bg-red-50 border border-red-100 rounded-full px-2.5 py-0.5">
                    +12%
                </span>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">892</h3>
                <p class="text-xs font-medium text-slate-400 mt-1">Total Pengaduan Bulan Ini</p>
            </div>
        </div>

        <!-- Card 4: Total Kegiatan Berjalan -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">directions_run</span>
                </div>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">12</h3>
                <p class="text-xs font-medium text-slate-400 mt-1">Total Kegiatan Berjalan</p>
            </div>
        </div>
    </div>

    <!-- Middle Row: User Statistics & Aktivitas Sistem -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Left 8 cols: User Statistics -->
        <div class="lg:col-span-8 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="mb-6">
                <h3 class="font-headline text-xl font-bold text-slate-900">User Statistics</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <!-- User Category Breakdown Gauge -->
                <div class="md:col-span-5 flex flex-col items-center">
                    <div class="relative w-40 h-40 flex items-center justify-center bg-slate-50 rounded-2xl border-4 border-t-[#0a1931] border-r-[#fd8b00] border-b-[#2b4f7c] border-l-[#ffdcb3] shadow-inner mb-5">
                        <div class="text-center">
                            <span class="font-headline text-3xl font-extrabold text-slate-900 block">1.2k</span>
                            <span class="text-xs font-medium text-slate-400">Users</span>
                        </div>
                    </div>

                    <!-- Category Legend -->
                    <div class="w-full space-y-2 text-xs font-medium">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#0a1931]"></span>
                                <span>Masyarakat</span>
                            </span>
                            <span class="text-slate-500 font-semibold">45%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#fd8b00]"></span>
                                <span>Petugas Lapangan</span>
                            </span>
                            <span class="text-slate-500 font-semibold">35%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#2b4f7c]"></span>
                                <span>Admin Satpol PP</span>
                            </span>
                            <span class="text-slate-500 font-semibold">15%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#ffdcb3]"></span>
                                <span>Super Admin</span>
                            </span>
                            <span class="text-slate-500 font-semibold">5%</span>
                        </div>
                    </div>
                </div>

                <!-- Growth Line Chart -->
                <div class="md:col-span-7">
                    <h4 class="text-xs font-bold text-slate-700 mb-4 text-center md:text-left">Pertumbuhan User Baru</h4>
                    <div class="relative w-full h-[220px]">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 4 cols: Aktivitas Sistem -->
        <div class="lg:col-span-4 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-headline text-xl font-bold text-slate-900">Aktivitas Sistem</h3>
                    <a href="#" class="text-xs font-bold text-brand-600 hover:underline">View All</a>
                </div>

                <!-- Timeline List -->
                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <!-- Activity 1 -->
                    <div class="relative">
                        <span class="absolute -left-6 top-1.5 w-3 h-3 rounded-full bg-brand-600 ring-4 ring-white"></span>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>10 mins ago</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600">USER MGMT</span>
                        </div>
                        <p class="text-sm font-medium text-slate-800 leading-snug">
                            Super Admin added User <span class="font-semibold text-brand-600">'Dedi Kurniawan'</span>
                        </p>
                    </div>

                    <!-- Activity 2 -->
                    <div class="relative">
                        <span class="absolute -left-6 top-1.5 w-3 h-3 rounded-full bg-amber-500 ring-4 ring-white"></span>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>45 mins ago</span>
                            <span class="px-2 py-0.5 rounded bg-amber-50 text-[10px] font-semibold text-amber-700 border border-amber-100">VALIDATION</span>
                        </div>
                        <p class="text-sm font-medium text-slate-800 leading-snug">
                            Admin Jakarta Pusat validated report <span class="font-semibold text-slate-900">#4092</span>
                        </p>
                    </div>

                    <!-- Activity 3 -->
                    <div class="relative">
                        <span class="absolute -left-6 top-1.5 w-3 h-3 rounded-full bg-blue-500 ring-4 ring-white"></span>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>2 hrs ago</span>
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-[10px] font-semibold text-blue-700 border border-blue-100">DISPATCH</span>
                        </div>
                        <p class="text-sm font-medium text-slate-800 leading-snug">
                            Emergency dispatch triggered for <span class="font-semibold text-slate-900">Kelapa Gading</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Personnel Statistics -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="mb-6">
            <h3 class="font-headline text-xl font-bold text-slate-900">Personnel Statistics</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left 6 cols: Distribusi Personil per Wilayah -->
            <div class="lg:col-span-7">
                <h4 class="text-xs font-bold text-slate-700 mb-4">Distribusi Personil per Wilayah</h4>

                <div class="space-y-4 text-xs font-medium">
                    <!-- Wilayah 1 -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-700 font-semibold">Jakarta Pusat</span>
                            <span class="text-slate-900 font-bold">120</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0a1931] rounded-full" style="width: 85%;"></div>
                        </div>
                    </div>

                    <!-- Wilayah 2 -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-700 font-semibold">Jakarta Selatan</span>
                            <span class="text-slate-900 font-bold">95</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0a1931] rounded-full" style="width: 70%;"></div>
                        </div>
                    </div>

                    <!-- Wilayah 3 -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-700 font-semibold">Jakarta Timur</span>
                            <span class="text-slate-900 font-bold">88</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0a1931] rounded-full" style="width: 62%;"></div>
                        </div>
                    </div>

                    <!-- Wilayah 4 -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-700 font-semibold">Jakarta Barat</span>
                            <span class="text-slate-900 font-bold">80</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0a1931] rounded-full" style="width: 55%;"></div>
                        </div>
                    </div>

                    <!-- Wilayah 5 -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-700 font-semibold">Jakarta Utara</span>
                            <span class="text-slate-900 font-bold">67</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#2b4f7c] rounded-full" style="width: 45%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 5 cols: Rata-rata Beban Tugas -->
            <div class="lg:col-span-5 bg-slate-50 border border-slate-200 rounded-xl p-6 flex flex-col justify-between h-full">
                <div>
                    <h4 class="font-semibold text-sm text-slate-800">Rata-rata Beban Tugas (Bulan Ini)</h4>
                    <p class="text-xs text-slate-400 mt-1">Average tasks assigned per active field officer.</p>

                    <div class="mt-5">
                        <div class="flex items-baseline gap-2">
                            <span class="font-headline text-4xl font-extrabold text-slate-900">14.5</span>
                            <span class="text-xs font-semibold text-slate-500">tugas/petugas</span>
                        </div>
                        <p class="text-xs font-semibold text-amber-600 mt-2 inline-flex items-center gap-1">
                            <span>📈</span> +1.2 dari bulan lalu
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200">
                    <div class="flex justify-between text-xs font-semibold mb-1.5">
                        <span class="text-slate-600">Kapasitas Optimal</span>
                        <span class="text-amber-600">85%</span>
                    </div>
                    <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-accent-500 rounded-full" style="width: 85%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('userGrowthChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
                datasets: [{
                    label: 'User Baru',
                    data: [50, 90, 70, 140, 120, 180],
                    borderColor: '#0a1931',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#64748b' }
                    },
                    y: {
                        min: 0,
                        max: 200,
                        ticks: { stepSize: 100, font: { family: 'Inter', size: 10 }, color: '#64748b' },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    });
</script>
@endpush
