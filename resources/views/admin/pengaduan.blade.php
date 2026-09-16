@extends('layouts.admin')

@section('title', 'Pengaduan & Informasi')

@section('content')
    <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Pengaduan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau semua laporan serta informasi dari masyarakat.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700">
                <span class="material-symbols-outlined text-[18px]">database</span>
                Data MySQL aktif
            </span>
            <a href="{{ route('admin.pengaduan.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white hover:bg-sidebar-hover">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah Laporan
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.pengaduan') }}" class="mb-6 grid grid-cols-1 items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        @if ($selectedType)<input type="hidden" name="type" value="{{ $selectedType->value }}">@endif
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="report-search">PENCARIAN</label><input id="report-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode, lokasi, telepon" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="report-status">STATUS</label><select id="report-status" name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Semua status</option>@foreach (\App\ReportStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="report-month">BULAN</label><input id="report-month" type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="report-start">MULAI</label><input id="report-start" type="date" name="date_start" value="{{ $filters['date_start'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="report-end">SAMPAI</label><input id="report-end" type="date" name="date_end" value="{{ $filters['date_end'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-sidebar-active px-4 py-2 text-sm font-semibold text-white">Terapkan</button><a href="{{ route('admin.pengaduan') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm">Reset</a></div>
    </form>

    <div class="grid grid-cols-1 gap-5 mb-8 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Total Masuk</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1">{{ $statistics['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-orange-100/70 text-orange-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">inbox</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Laporan Darurat</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1">{{ $statistics['emergency'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100/70 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">emergency</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Informasi & Pengaduan</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1">{{ $statistics['information'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-brand-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">info</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Selesai</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1">{{ $statistics['completed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">task_alt</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex flex-col gap-3 px-6 py-4 border-b border-slate-100 md:flex-row md:items-center md:justify-between">
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.pengaduan') }}"
                    class="text-sm font-semibold px-4 py-2 rounded-xl border transition-colors {{ $selectedType === null ? 'bg-sidebar-active text-white border-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Semua
                </a>
                <a href="{{ route('admin.pengaduan', ['type' => \App\ReportType::Emergency->value]) }}"
                    class="text-sm font-semibold px-4 py-2 rounded-xl border transition-colors {{ $selectedType === \App\ReportType::Emergency ? 'bg-sidebar-active text-white border-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Laporan Darurat
                </a>
                <a href="{{ route('admin.pengaduan', ['type' => \App\ReportType::Information->value]) }}"
                    class="text-sm font-semibold px-4 py-2 rounded-xl border transition-colors {{ $selectedType === \App\ReportType::Information ? 'bg-sidebar-active text-white border-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Informasi & Pengaduan
                </a>
            </div>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">
                {{ $reports->total() }} laporan ditemukan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                        <th class="px-6 py-3.5 font-semibold text-left">No. Laporan</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Jenis</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Sumber</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Kategori</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Lokasi</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Telepon</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        @php
                            $typeClass = $report->type === \App\ReportType::Emergency
                                ? 'bg-orange-100 text-orange-700'
                                : 'bg-blue-100 text-blue-700';
                            $statusClass = match ($report->status) {
                                \App\ReportStatus::Completed => 'bg-emerald-100 text-emerald-700',
                                \App\ReportStatus::InProgress => 'bg-amber-100 text-amber-700',
                                \App\ReportStatus::Received, \App\ReportStatus::Verifying => 'bg-blue-100 text-blue-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3.5">
                                <div class="font-medium text-slate-800">{{ $report->code }}</div>
                                <div class="mt-0.5 text-[11px] text-slate-400">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $typeClass }}">{{ $report->type->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                @if ($report->source === \App\ReportSource::Offline)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border bg-slate-100 text-slate-600 border-slate-200">Offline</span>
                                @else
                                    <span class="text-xs font-medium text-slate-500">
                                        <span class="material-symbols-outlined text-[14px] align-middle">public</span>
                                        {{ $report->source_platform?->label() ?? $report->source->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $report->category?->label() ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-slate-500 max-w-xs truncate" title="{{ $report->location_address }}">{{ $report->location_address }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $report->reporter_phone }}</td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">{{ $report->status->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.pengaduan.detail', $report) }}" class="inline-flex items-center text-sidebar-active hover:text-sidebar-hover" title="Lihat detail laporan">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">📭</div>
                                <p>Belum ada laporan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection
