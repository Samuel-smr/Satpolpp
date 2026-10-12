@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Dashboard Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan operasional dan status pengaduan warga.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.dashboard') }}" method="GET" class="mb-8 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
            <div>
                <label for="period" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Periode statistik</label>
                <select id="period" name="period" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
                    <option value="1" @selected($dashboardFilters['period'] === '1')>1 hari terakhir</option>
                    <option value="3" @selected($dashboardFilters['period'] === '3')>3 hari terakhir</option>
                    <option value="7" @selected($dashboardFilters['period'] === '7')>7 hari terakhir</option>
                    <option value="custom" @selected($dashboardFilters['period'] === 'custom')>Rentang tanggal</option>
                </select>
            </div>
            <div>
                <label for="date_start" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Dari tanggal</label>
                <input id="date_start" name="date_start" type="date" value="{{ $dashboardFilters['period'] === 'custom' ? $dashboardFilters['date_start'] : '' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
            </div>
            <div>
                <label for="date_end" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Sampai tanggal</label>
                <input id="date_end" name="date_end" type="date" value="{{ $dashboardFilters['period'] === 'custom' ? $dashboardFilters['date_end'] : '' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
            </div>
            <button type="submit" class="flex items-center justify-center gap-2 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-sidebar-hover">
                <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                Terapkan Filter
            </button>
        </div>
    </form>

    <!-- Summary Metric Cards (4 Cards Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Urgent -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">{{ $periodLabel }}</p>
                    <h2 class="font-headline text-3xl font-bold text-red-600 mt-3">{{ $dashboardStats['urgent_today'] }}</h2>
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
                    <h2 class="font-headline text-3xl font-bold text-amber-600 mt-3">{{ $dashboardStats['pending'] }}</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100/70 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">assignment_late</span>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <span class="text-xs font-medium text-slate-400">Butuh atensi</span>
            </div>
        </div>

        <!-- Card 3: Pegawai e-Profil -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 max-w-[140px] leading-snug">Pegawai e-Profil</p>
                    <h2 class="font-headline text-3xl font-bold text-slate-900 mt-3">{{ $dashboardStats['active_officers'] }}</h2>
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
                    <h2 class="font-headline text-3xl font-bold text-slate-900 mt-3">{{ $dashboardStats['activities_this_week'] }}</h2>
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
                    <h3 class="font-headline text-xl font-bold text-slate-900">Statistik Pengaduan</h3>
                </div>
                <div class="flex items-center gap-4">
                    <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $dashboardFilters['date_start'] }} – {{ $dashboardFilters['date_end'] }}</span>
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

        <!-- Right 4 cols: Laporan Terbaru -->
        <div class="lg:col-span-4 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-headline text-xl font-bold text-slate-900">Laporan Terbaru</h3>
                    <a href="{{ route('admin.pengaduan') }}" class="text-xs font-bold text-sidebar-active hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-4">
                    @forelse ($latestReports as $latestReport)
                        @php
                            $latestStatusClass = match ($latestReport->status) {
                                \App\ReportStatus::Completed => 'bg-emerald-50 text-emerald-700',
                                \App\ReportStatus::InProgress => 'bg-amber-50 text-amber-700',
                                \App\ReportStatus::Received, \App\ReportStatus::Verifying => 'bg-blue-50 text-blue-700',
                                \App\ReportStatus::Rejected => 'bg-red-50 text-red-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <a href="{{ route('admin.pengaduan.detail', $latestReport) }}" class="block rounded-xl border border-slate-200 border-l-4 border-l-sidebar-active bg-white p-4 transition-shadow hover:shadow-md">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="truncate text-sm font-semibold text-slate-800">{{ $latestReport->category?->label() ?? $latestReport->code }}</h4>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $latestReport->location_address }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $latestStatusClass }}">{{ $latestReport->status->label() }}</span>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">{{ $latestReport->code }} · {{ $latestReport->created_at->format('d/m/Y H:i') }}</p>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-400">Belum ada laporan pada periode ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.pengaduan') }}" class="flex w-full items-center justify-center rounded-xl border border-slate-300 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Buka Daftar Pengaduan
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('weeklyChart').getContext('2d');

        const weeklyChart = @json($weeklyChart);
        const labels = weeklyChart.map((day) => day.label);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Diselesaikan',
                        data: weeklyChart.map((day) => day.completed),
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
                        data: weeklyChart.map((day) => day.incoming),
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
