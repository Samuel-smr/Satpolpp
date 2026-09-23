@extends($reportLayout ?? 'layouts.admin')

@section('title', 'Detail Pengaduan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@section('content')
    @php
        $statusClass = match ($report->status) {
            \App\ReportStatus::Completed => 'bg-emerald-100 text-emerald-700',
            \App\ReportStatus::InProgress => 'bg-amber-100 text-amber-700',
            \App\ReportStatus::Received, \App\ReportStatus::Verifying => 'bg-blue-100 text-blue-700',
            \App\ReportStatus::Rejected => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
    @endphp

    <a href="{{ route($reportBackRoute ?? 'admin.pengaduan') }}" class="inline-flex items-center gap-2 text-sidebar-active font-medium hover:underline mb-6">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span>{{ ($reportReadOnly ?? false) ? 'Kembali ke daftar kegiatan' : 'Kembali ke daftar pengaduan' }}</span>
    </a>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor laporan</p>
                        <h1 class="font-headline font-bold text-xl text-slate-800">{{ $report->code }}</h1>
                    </div>
                    <span class="self-start text-sm font-semibold px-3 py-1 rounded-full {{ $statusClass }}">{{ $report->status->label() }}</span>
                </div>

                <dl class="grid gap-4 text-sm bg-slate-50 border border-slate-200 rounded-xl p-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-400">Sumber</dt>
                        <dd class="font-medium text-slate-800">
                            {{ $report->source->label() }}{{ $report->source_platform ? ' · '.$report->source_platform->label() : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Waktu laporan</dt>
                        <dd class="font-medium text-slate-800">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nama pelapor</dt>
                        <dd class="font-medium text-slate-800">{{ $report->reporter_name ?: 'Tidak dicantumkan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nomor telepon</dt>
                        <dd class="font-medium text-slate-800">{{ $report->reporter_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">NIK</dt>
                        <dd class="font-medium text-slate-800">{{ $report->maskedReporterNik() ?? 'Tidak dicantumkan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Latitude</dt>
                        <dd class="font-medium text-slate-800">{{ $report->latitude ?? 'Tidak dicantumkan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Longitude</dt>
                        <dd class="font-medium text-slate-800">{{ $report->longitude ?? 'Tidak dicantumkan' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400">Lokasi</dt>
                        <dd class="font-medium text-slate-800 whitespace-pre-line">{{ $report->location_address }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400">Deskripsi</dt>
                        <dd class="font-medium text-slate-800 whitespace-pre-line">{{ $report->description }}</dd>
                    </div>
                </dl>
            </section>

            @if ($report->activity !== null)
                <section id="kegiatan-penanganan" class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 shadow-sm">
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Kegiatan eksternal</p>
                            <h2 class="font-headline text-lg font-bold text-slate-800">{{ $report->activity->name }}</h2>
                        </div>
                        <span class="self-start rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Terhubung ke {{ $report->code }}</span>
                    </div>

                    <dl class="grid gap-4 rounded-xl border border-emerald-100 bg-white p-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Pelaksanaan</dt>
                            <dd class="font-medium text-slate-800">
                                {{ $report->activity->activity_date->format('d/m/Y') }}
                                @if ($report->activity->start_time !== null)
                                    · {{ $report->activity->start_time->format('H:i') }} WIB
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Lokasi</dt>
                            <dd class="whitespace-pre-line font-medium text-slate-800">{{ $report->activity->location_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Latitude</dt>
                            <dd class="font-medium text-slate-800">{{ $report->activity->latitude }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Longitude</dt>
                            <dd class="font-medium text-slate-800">{{ $report->activity->longitude }}</dd>
                        </div>
                        @if ($report->activity->description)
                            <div class="sm:col-span-2">
                                <dt class="text-slate-400">Deskripsi kegiatan</dt>
                                <dd class="whitespace-pre-line font-medium text-slate-800">{{ $report->activity->description }}</dd>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Hasil penanganan</dt>
                            <dd class="whitespace-pre-line font-medium text-slate-800">{{ $report->activity->handling_result }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <h3 class="mb-2 text-sm font-semibold text-slate-700">Temuan</h3>
                        <div class="space-y-2">
                            @forelse ($report->activity->findings as $finding)
                                <div class="grid grid-cols-1 gap-3 rounded-xl border sm:grid-cols-3 border-emerald-100 bg-white/70 p-3 text-sm">
                                    <span>{{ $finding->type->label() }}</span>
                                    <span class="font-semibold">{{ $finding->quantity }}</span>
                                    <span>{{ $finding->unit->label() }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Tidak ada temuan.</p>
                            @endforelse
                        </div>
                    </div>

                    @if ($report->status === \App\ReportStatus::Completed)
                        <div class="mt-5">
                            <h3 class="mb-2 text-sm font-semibold text-slate-700">Peta lokasi penanganan</h3>
                            <div id="completed-activity-map" class="h-80 overflow-hidden rounded-xl border border-emerald-100 bg-slate-100" aria-label="Peta lokasi penanganan yang telah selesai"></div>
                        </div>
                    @endif

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <h3 class="mb-2 text-sm font-semibold text-slate-700">Petugas</h3>
                            <div class="space-y-2">
                                @foreach ($report->activity->officers as $officer)
                                    <div class="rounded-xl border border-emerald-100 bg-white px-4 py-3 text-sm">
                                        <p class="font-semibold text-slate-700">{{ $officer->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $officer->employee_number }}{{ $officer->position ? ' · '.$officer->position : '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-sm font-semibold text-slate-700">Bukti penanganan</h3>
                            <div class="grid gap-3">
                                @forelse ($report->activity->attachments as $attachment)
                                    <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white text-sm">
                                        <a href="{{ route($activityAttachmentRoute ?? 'admin.kegiatan.export-attachments.show', [$report->activity, $attachment]) }}" target="_blank" rel="noopener" class="block aspect-video bg-slate-100">
                                            <img src="{{ route($activityAttachmentRoute ?? 'admin.kegiatan.export-attachments.show', [$report->activity, $attachment]) }}" alt="Bukti penanganan {{ $attachment->original_name }}" loading="lazy" class="h-full w-full object-cover transition-transform hover:scale-105">
                                        </a>
                                        <div class="px-4 py-3">
                                            <p class="truncate font-semibold text-slate-700" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                            <p class="text-xs text-slate-400">{{ $attachment->mime_type }} · {{ \App\Support\FileSize::format($attachment->file_size) }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-emerald-200 bg-white p-6 text-center text-sm text-slate-400">
                                        Tidak ada foto bukti penanganan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            @elseif ($report->status === \App\ReportStatus::InProgress && ! ($reportReadOnly ?? false))
                @php
                    $selectedOfficerIds = array_map('strval', old('officer_ids', []));
                @endphp
                <section id="form-penyelesaian" class="overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50/60 shadow-sm">
                    <div class="px-6 pt-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <span class="material-symbols-outlined text-[24px]">task_alt</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Penyelesaian laporan</p>
                                <h2 class="font-headline text-lg font-bold text-slate-800">Selesaikan Laporan</h2>
                                <p class="mt-1 text-sm text-slate-500">Lengkapi data kegiatan, petugas, dan bukti penanganan untuk membuat kegiatan eksternal yang tetap terhubung ke laporan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                    <form action="{{ route('admin.pengaduan.complete.store', $report) }}" method="POST" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-emerald-100 bg-white p-6">
                        @csrf
                        <input type="hidden" name="current_status" value="{{ $report->status->value }}">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="activity_template_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Template kegiatan (opsional)</label>
                                <select id="activity_template_id" name="activity_template_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                                    <option value="">Tanpa template</option>
                                    @foreach ($activityTemplates as $template)
                                        <option value="{{ $template->id }}" data-name="{{ $template->name }}" data-description="{{ $template->default_description }}" @selected((string) old('activity_template_id') === (string) $template->id)>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="activity_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama kegiatan</label>
                                <input id="activity_name" name="name" type="text" maxlength="255" required value="{{ old('name', 'Penanganan '.$report->code) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div>
                                <label for="activity_date" class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal pelaksanaan</label>
                                <input id="activity_date" name="activity_date" type="date" max="{{ now()->toDateString() }}" required value="{{ old('activity_date', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div>
                                <label for="start_time" class="mb-1.5 block text-sm font-semibold text-slate-700">Waktu mulai (opsional)</label>
                                <input id="start_time" name="start_time" type="time" value="{{ old('start_time') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div>
                                <label for="location_address" class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi kegiatan</label>
                                <input id="location_address" name="location_address" type="text" maxlength="2000" required value="{{ old('location_address', $report->location_address) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih titik lokasi penanganan</label>
                                <x-location-map-controls prefix="completion" />
                                <div id="completion-map" class="h-72 overflow-hidden rounded-xl border border-slate-200 bg-slate-100" aria-label="Peta lokasi penanganan"></div>
                                <p class="mt-1.5 text-xs text-slate-400">Cari lokasi, gunakan lokasi saat ini, atau klik peta. Koordinat akan terisi otomatis.</p>
                            </div>
                            <div>
                                <label for="latitude" class="mb-1.5 block text-sm font-semibold text-slate-700">Latitude</label>
                                <input id="latitude" name="latitude" type="number" min="-90" max="90" step="0.0000001" required value="{{ old('latitude', $report->latitude) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div>
                                <label for="longitude" class="mb-1.5 block text-sm font-semibold text-slate-700">Longitude</label>
                                <input id="longitude" name="longitude" type="number" min="-180" max="180" step="0.0000001" required value="{{ old('longitude', $report->longitude) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="activity_description" class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi kegiatan (opsional)</label>
                                <textarea id="activity_description" name="description" rows="3" maxlength="5000" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('description') }}</textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="handling_result" class="mb-1.5 block text-sm font-semibold text-slate-700">Hasil penanganan</label>
                                <textarea id="handling_result" name="handling_result" rows="4" maxlength="5000" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('handling_result') }}</textarea>
                            </div>
                        </div>

                        <x-activity-findings-fields prefix="completion" :findings="old('findings', [])" />

                        <fieldset>
                            <legend class="mb-3 text-sm font-semibold text-slate-700">Petugas yang menangani</legend>
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                    <label for="completion-officer-search" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Daftar petugas aktif</label>
                                    <div class="relative mb-3">
                                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
                                        <input id="completion-officer-search" type="search" placeholder="Cari nama atau nomor petugas..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm outline-none transition focus:border-sidebar-active focus:ring-2 focus:ring-sidebar-active/20">
                                    </div>
                                    <div id="completion-officer-list" class="max-h-64 space-y-2 overflow-y-auto pr-1">
                                        @forelse ($activeOfficers as $officer)
                                            <label data-completion-officer-item data-search-text="{{ strtolower($officer->name.' '.$officer->employee_number.' '.$officer->position) }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition-colors hover:border-sidebar-active/40 hover:bg-blue-50/40">
                                                <input type="checkbox" name="officer_ids[]" value="{{ $officer->id }}" @checked(in_array((string) $officer->id, $selectedOfficerIds, true)) data-completion-officer-checkbox data-name="{{ $officer->name }}" data-employee-number="{{ $officer->employee_number }}" data-position="{{ $officer->position }}" class="mt-1 rounded border-slate-300 text-sidebar-active focus:ring-sidebar-active">
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-semibold text-slate-700">{{ $officer->name }}</span>
                                                    <span class="block truncate text-xs text-slate-400">{{ $officer->employee_number }}{{ $officer->position ? ' · '.$officer->position : '' }}</span>
                                                </span>
                                            </label>
                                        @empty
                                            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">Belum ada petugas aktif. Laporan belum dapat diselesaikan.</p>
                                        @endforelse
                                    </div>
                                    <p id="completion-officer-search-empty" class="hidden rounded-xl border border-dashed border-slate-200 bg-white p-4 text-center text-sm text-slate-400">Petugas tidak ditemukan.</p>
                                </div>

                                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <h3 class="text-xs font-semibold uppercase tracking-wider text-blue-700">Petugas terpilih</h3>
                                        <span id="completion-selected-officer-count" class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-blue-600">0</span>
                                    </div>
                                    <div id="completion-selected-officer-list" class="max-h-64 space-y-2 overflow-y-auto pr-1">
                                        <p id="completion-selected-officer-empty" class="rounded-xl border border-dashed border-blue-200 bg-white/70 p-4 text-center text-sm text-blue-400">Belum ada petugas dipilih.</p>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div>
                            <label for="evidence" class="mb-1.5 block text-sm font-semibold text-slate-700">Foto bukti penanganan</label>
                            <input id="evidence" name="evidence[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required class="block w-full rounded-xl border border-slate-200 p-2 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold file:text-slate-700">
                            <p class="mt-1.5 text-xs text-slate-400">1–3 foto, format JPG/JPEG/PNG/WEBP, maksimal 5 MB per foto.</p>
                        </div>

                        <button type="submit" @disabled($activeOfficers->isEmpty()) class="w-full rounded-xl bg-emerald-600 py-3 font-semibold text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                            Selesaikan dan Buat Kegiatan Eksternal
                        </button>
                    </form>
                    </div>
                </section>
            @endif

            <section class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="font-headline font-bold text-lg text-slate-800">Lampiran Laporan</h2>
                    <span class="text-xs font-semibold text-slate-500">{{ $report->attachments->count() }} file</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($report->attachments as $attachment)
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                            @if ($attachment->file_type === 'image' && in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true))
                                <a href="{{ route($reportAttachmentRoute ?? 'admin.pengaduan.attachments.show', [$report, $attachment]) }}" target="_blank" rel="noopener" class="block aspect-video bg-slate-100">
                                    <img src="{{ route($reportAttachmentRoute ?? 'admin.pengaduan.attachments.show', [$report, $attachment]) }}" alt="Lampiran {{ $attachment->original_name }}" class="h-full w-full object-cover transition-transform hover:scale-105">
                                </a>
                            @else
                                <div class="flex h-32 items-center justify-center bg-white text-sidebar-active">
                                    <span class="material-symbols-outlined text-4xl">description</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-3 p-4">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-700" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $attachment->mime_type }} · {{ \App\Support\FileSize::format($attachment->file_size) }}</p>
                                </div>
                                @if ($attachment->file_type === 'image')
                                    <a href="{{ route($reportAttachmentRoute ?? 'admin.pengaduan.attachments.show', [$report, $attachment]) }}" target="_blank" rel="noopener" title="Buka lampiran" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                        <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400 sm:col-span-2">
                            Tidak ada lampiran pada laporan ini.
                        </div>
                    @endforelse
                </div>

                @if ($report->attachments->isNotEmpty())
                    <p class="mt-3 text-xs text-slate-400">Klik foto untuk membuka lampiran dalam ukuran penuh.</p>
                @endif
            </section>

            <section class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <h2 class="font-headline font-bold text-lg text-slate-800 mb-5">Riwayat Status</h2>

                <ol class="relative ml-2">
                    @forelse ($report->statusHistories as $history)
                        <li class="relative pb-7 pl-8 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute bottom-0 left-0 top-5 w-0.5 bg-slate-200"></span>
                            @endunless
                            <span class="absolute left-0 top-1 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-white bg-emerald-500 ring-2 ring-emerald-500"></span>
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ $history->status->label() }}</h3>
                                    <p class="text-sm text-slate-500">{{ $history->note ?: $history->status->description() }}</p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Oleh {{ $history->changedByUser?->name ?? 'Sistem' }}
                                    </p>
                                </div>
                                <time class="text-xs text-slate-400" datetime="{{ $history->created_at->toIso8601String() }}">
                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                </time>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                            Riwayat status belum tersedia.
                        </li>
                    @endforelse
                </ol>
            </section>

            @if ($report->feedback_submitted_at !== null)
                <section class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                    <h2 class="font-headline font-bold text-lg text-slate-800 mb-3">Feedback Pelapor</h2>
                    <div class="flex items-center gap-1 text-amber-500" aria-label="Rating {{ $report->feedback_rating }} dari 5">
                        @for ($star = 1; $star <= 5; $star++)
                            <span class="material-symbols-outlined text-[20px]">{{ $star <= $report->feedback_rating ? 'star' : 'star_outline' }}</span>
                        @endfor
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $report->feedback_comment ?: 'Tidak ada komentar.' }}</p>
                </section>
            @endif
        </div>

        <aside>
            <div class="sticky top-24 rounded-2xl border bg-slate-800 p-6 text-white shadow-sm">
                <h2 class="font-headline font-bold mb-2">Tindak Lanjut</h2>
                @if ($reportReadOnly ?? false)
                    <p class="text-sm text-slate-300">Detail laporan ini ditampilkan dalam mode baca-saja untuk Super Admin.</p>
                    <a href="{{ route('super_admin.kegiatan', ['type' => 'external']) }}" class="mt-5 block w-full rounded-xl bg-white/10 py-2.5 text-center font-medium text-white hover:bg-white/20">Kembali ke Kegiatan</a>
                @elseif ($report->status->nextProcessingStatus() !== null)
                    <p class="text-sm text-slate-300">
                        Lanjutkan ke status <strong class="text-white">{{ $report->status->nextProcessingStatus()->label() }}</strong>.
                    </p>

                    <form action="{{ route('admin.pengaduan.status.update', $report) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="current_status" value="{{ $report->status->value }}">
                        <div>
                            <label for="note" class="mb-1.5 block text-xs font-semibold text-slate-300">Catatan status (opsional)</label>
                            <textarea id="note" name="note" rows="3" maxlength="1000" placeholder="Tambahkan catatan untuk riwayat laporan..."
                                class="w-full rounded-xl border border-white/10 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-sidebar-active py-2.5 font-semibold text-white transition-colors hover:bg-sidebar-hover">
                            {{ $report->status->nextProcessingStatus()->label() }}
                        </button>
                    </form>

                    @if ($report->status === \App\ReportStatus::Verifying)
                        <form action="{{ route('admin.pengaduan.reject', $report) }}" method="POST" class="mt-5 space-y-4 border-t border-white/10 pt-5">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="current_status" value="{{ $report->status->value }}">
                            <div>
                                <label for="reject-reason" class="mb-1.5 block text-xs font-semibold text-red-300">Alasan penolakan <span class="text-red-400">*</span></label>
                                <textarea id="reject-reason" name="alasan_tolak" rows="3" maxlength="1000" required placeholder="Contoh: lokasi tidak ditemukan, data pelapor tidak valid, atau bukan kewenangan Satpol PP..."
                                    class="w-full rounded-xl border border-red-400/30 bg-red-500/10 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400">{{ old('alasan_tolak') }}</textarea>
                            </div>
                            <button type="submit"
                                class="w-full rounded-xl bg-red-600 py-2.5 font-semibold text-white transition-colors hover:bg-red-700"
                                data-confirm-delete="Tolak laporan ini? Pelapor akan melihat alasan penolakan dan laporan tidak dilanjutkan ke penanganan.">
                                Tolak Laporan
                            </button>
                        </form>
                    @endif
                @elseif ($report->status === \App\ReportStatus::InProgress)
                    <p class="text-sm text-slate-300">Lengkapi data kegiatan, petugas, dan bukti penanganan sebelum menyelesaikan laporan.</p>
                    <a href="#form-penyelesaian" class="mt-5 block w-full rounded-xl bg-emerald-600 py-2.5 text-center font-semibold text-white transition-colors hover:bg-emerald-700">Isi Form Penyelesaian</a>
                @elseif ($report->status === \App\ReportStatus::Rejected)
                    <p class="text-sm text-slate-300">Laporan ini telah <strong class="text-red-400">ditolak</strong> oleh petugas.</p>
                    @php
                        $rejection = $report->statusHistories->firstWhere('status', \App\ReportStatus::Rejected);
                    @endphp
                    @if ($rejection?->note)
                        <div class="mt-3 rounded-xl border border-red-400/30 bg-red-500/10 p-4">
                            <p class="text-xs font-semibold text-red-400 uppercase tracking-wider">Alasan penolakan</p>
                            <p class="mt-1 text-sm text-white">{{ $rejection->note }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-300">Laporan ini sudah selesai ditangani dan tercatat sebagai kegiatan eksternal.</p>
                    @if ($report->activity !== null)
                        <a href="{{ route('admin.kegiatan', ['type' => 'external']) }}" class="mt-5 block w-full rounded-xl bg-white/10 py-2.5 text-center font-medium text-white hover:bg-white/20">Lihat Kegiatan</a>
                    @endif
                @endif

                @unless ($reportReadOnly ?? false)
                    <form method="POST" action="{{ route('admin.pengaduan.destroy', $report) }}" class="mt-5" data-confirm-delete="Hapus laporan ini? Riwayat dan berkas tetap disimpan untuk audit, tetapi laporan tidak dapat diakses lagi.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-xl border border-red-400/40 py-2.5 font-medium text-red-300 transition-colors hover:bg-red-500/10 hover:text-red-200">Hapus Laporan</button>
                    </form>
                @endunless

                <div class="mt-5 border-t border-white/10 pt-5">
                    <p class="text-xs text-slate-400">Status saat ini</p>
                    <p class="mt-1 font-semibold text-white">{{ $report->status->label() }}</p>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        @if ($report->status === \App\ReportStatus::InProgress && ! ($reportReadOnly ?? false))
        document.querySelectorAll('a[href="#form-penyelesaian"]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const target = document.getElementById('form-penyelesaian');
                const main = document.querySelector('main');

                if (target && main) {
                    main.scrollTo({
                        top: main.scrollTop + target.getBoundingClientRect().top - main.getBoundingClientRect().top,
                        behavior: 'smooth',
                    });
                } else {
                    target?.scrollIntoView({ block: 'start' });
                }
            });
        });
        @endif

        const activityTemplate = document.getElementById('activity_template_id');

        activityTemplate?.addEventListener('change', function () {
            const selectedTemplate = this.options[this.selectedIndex];

            if (!selectedTemplate.value) {
                return;
            }

            document.getElementById('activity_name').value = selectedTemplate.dataset.name || '';
            document.getElementById('activity_description').value = selectedTemplate.dataset.description || '';
        });

        const completionOfficerSearch = document.getElementById('completion-officer-search');
        const completionOfficerItems = Array.from(document.querySelectorAll('[data-completion-officer-item]'));
        const completionOfficerCheckboxes = Array.from(document.querySelectorAll('[data-completion-officer-checkbox]'));
        const completionSelectedOfficerList = document.getElementById('completion-selected-officer-list');
        const completionSelectedOfficerCount = document.getElementById('completion-selected-officer-count');
        const completionSelectedOfficerEmpty = document.getElementById('completion-selected-officer-empty');
        const completionOfficerSearchEmpty = document.getElementById('completion-officer-search-empty');

        const renderCompletionOfficers = () => {
            if (!completionSelectedOfficerList || !completionSelectedOfficerCount) {
                return;
            }

            completionSelectedOfficerList.querySelectorAll('[data-selected-completion-officer]').forEach((item) => item.remove());
            const selected = completionOfficerCheckboxes.filter((checkbox) => checkbox.checked);
            completionSelectedOfficerCount.textContent = selected.length;
            completionSelectedOfficerEmpty?.classList.toggle('hidden', selected.length > 0);

            selected.forEach((checkbox) => {
                const item = document.createElement('div');
                item.dataset.selectedCompletionOfficer = 'true';
                item.className = 'flex items-center gap-3 rounded-xl border border-blue-100 bg-white p-3';

                const text = document.createElement('div');
                text.className = 'min-w-0 flex-1';
                const name = document.createElement('p');
                name.className = 'truncate text-sm font-semibold text-slate-700';
                name.textContent = checkbox.dataset.name || '-';
                text.appendChild(name);

                const metadata = document.createElement('p');
                metadata.className = 'mt-0.5 truncate text-xs text-slate-400';
                metadata.textContent = [checkbox.dataset.employeeNumber, checkbox.dataset.position].filter(Boolean).join(' · ');
                text.appendChild(metadata);
                item.appendChild(text);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.title = 'Hapus dari petugas terpilih';
                removeButton.className = 'rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600';
                removeButton.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
                removeButton.addEventListener('click', () => {
                    checkbox.checked = false;
                    renderCompletionOfficers();
                });
                item.appendChild(removeButton);
                completionSelectedOfficerList.appendChild(item);
            });
        };

        completionOfficerCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', renderCompletionOfficers));

        completionOfficerSearch?.addEventListener('input', () => {
            const query = completionOfficerSearch.value.trim().toLowerCase();
            let visibleCount = 0;

            completionOfficerItems.forEach((item) => {
                const isVisible = item.dataset.searchText.includes(query);
                item.classList.toggle('hidden', !isVisible);
                visibleCount += isVisible ? 1 : 0;
            });

            completionOfficerSearchEmpty?.classList.toggle('hidden', visibleCount > 0);
        });

        renderCompletionOfficers();

        const completedActivityMapElement = document.getElementById('completed-activity-map');

        if (window.L && completedActivityMapElement) {
            const completedLatitude = @js($report->activity?->latitude);
            const completedLongitude = @js($report->activity?->longitude);
            const completedActivityMap = L.map(completedActivityMapElement);

            if (window.KulonProgoMap) {
                window.KulonProgoMap.setup(completedActivityMap);
            } else {
                completedActivityMap.setView([completedLatitude, completedLongitude], 15);
            }

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(completedActivityMap);
            L.marker([completedLatitude, completedLongitude]).addTo(completedActivityMap);
            completedActivityMap.setView([completedLatitude, completedLongitude], 15);
        }

        if (window.L && document.getElementById('completion-map')) {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const map = L.map('completion-map');

            if (window.KulonProgoMap) {
                window.KulonProgoMap.setup(map);
            } else {
                map.setView([-7.75, 110.16], 11);
            }

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            window.KulonProgoMap?.createLocationPicker({
                map,
                latitudeInput,
                longitudeInput,
                addressInput: document.getElementById('location_address'),
                searchInput: document.getElementById('completion-location-search'),
                searchButton: document.getElementById('completion-location-search-button'),
                currentLocationButton: document.getElementById('completion-current-location-button'),
                statusElement: document.getElementById('completion-location-status')
            });
        }
    </script>
@endpush
