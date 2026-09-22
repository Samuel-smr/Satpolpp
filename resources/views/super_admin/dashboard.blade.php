@extends('layouts.super_admin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Super Admin Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Ringkasan operasi sistem dan log aktivitas terkini.</p>
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
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">Database</span>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">{{ $systemStats['users'] }}</h3>
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
                <h3 class="font-headline text-3xl font-bold text-slate-900">{{ $systemStats['active_officers'] }}</h3>
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
                    Database
                </span>
            </div>
            <div>
                <h3 class="font-headline text-3xl font-bold text-slate-900">{{ $systemStats['monthly_reports'] }}</h3>
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
                <h3 class="font-headline text-3xl font-bold text-slate-900">{{ $systemStats['monthly_activities'] }}</h3>
                <p class="text-xs font-medium text-slate-400 mt-1">Total Kegiatan Berjalan</p>
            </div>
        </div>
    </div>

    <!-- Log Aktivitas Sistem -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="font-headline text-xl font-bold text-slate-900">Log Aktivitas Sistem</h3>
                <p class="text-xs text-slate-400">Audit trail terbaru aktivitas SIPRAJA.</p>
            </div>
            <a href="{{ route('super_admin.monitoring') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                <span class="material-symbols-outlined text-[18px]">monitoring</span>
                Buka Monitoring Sistem
            </a>
        </div>
        <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto" id="log-list"></div>
    </div>

    <div class="mt-8">
        @include('super_admin.peta')
    </div>
@endsection

@push('scripts')
<script>
    const databaseLogs = @json($logs);

    function jenisClass(j) {
        if (j === 'auth') return 'bg-purple-100 text-purple-700';
        if (j === 'reports') return 'bg-blue-100 text-blue-700';
        if (j === 'organization') return 'bg-slate-200 text-slate-700';
        return 'bg-emerald-100 text-emerald-700';
    }

    function iconJenis(j) {
        if (j === 'auth') return 'lock';
        if (j === 'reports') return 'description';
        if (j === 'organization') return 'account_balance';
        return 'badge';
    }

    function renderLogs() {
        const logs = databaseLogs || [];
        if (logs.length === 0) {
            document.getElementById('log-list').innerHTML = '<div class="px-6 py-12 text-center text-sm text-slate-400">Belum ada aktivitas sistem yang tercatat.</div>';
            return;
        }
        document.getElementById('log-list').innerHTML = logs.map(l => `
            <div class="flex items-center gap-3 px-6 py-3">
                <div class="w-8 h-8 rounded-full ${jenisClass(l.jenis)} flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">${iconJenis(l.jenis)}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-700 truncate">${escapeHtml(l.aksi)}</p>
                    <p class="text-xs text-slate-400">oleh ${escapeHtml(l.user)}</p>
                </div>
                <span class="text-xs text-slate-400 font-mono flex-shrink-0">${escapeHtml(l.waktu)}</span>
            </div>
        `).join('');
    }

    renderLogs();
</script>
@endpush
