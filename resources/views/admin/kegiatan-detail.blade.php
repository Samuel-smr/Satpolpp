@extends('layouts.admin')

@section('title', 'Detail Kegiatan Internal')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@section('content')
    <div class="fixed inset-y-0 left-64 right-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="activity-detail-modal-title">
        <a href="{{ route('admin.kegiatan') }}" class="absolute inset-0 bg-black/40" aria-label="Tutup detail kegiatan"></a>

        <div class="relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 flex-col gap-4 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span class="inline-flex rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600">Kegiatan Internal</span>
                    <h1 id="activity-detail-modal-title" class="mt-2 font-headline text-xl font-bold text-slate-900">{{ $activity->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Dibuat oleh {{ $activity->createdByUser->name }} pada {{ $activity->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-start">
                    <a href="{{ route('admin.kegiatan.edit', $activity) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-sidebar-hover">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        Edit Kegiatan
                    </a>
                    <form action="{{ route('admin.kegiatan.destroy', $activity) }}" method="POST" data-confirm-delete="Hapus kegiatan ini? Data akan disembunyikan dari daftar, tetapi tetap tersimpan sebagai arsip.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm transition-colors hover:bg-red-50">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            Hapus
                        </button>
                    </form>
                    <a href="{{ route('admin.kegiatan') }}" aria-label="Tutup detail kegiatan" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </a>
                </div>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto p-6">

                <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Informasi Kegiatan</h2>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Kategori operasional</dt>
                            <dd class="font-medium text-slate-800">{{ $activity->operational_category?->label() ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Template</dt>
                            <dd class="font-medium text-slate-800">{{ $activity->template?->name ?? 'Tanpa template' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Tanggal pelaksanaan</dt>
                            <dd class="font-medium text-slate-800">{{ $activity->activity_date->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Waktu mulai</dt>
                            <dd class="font-medium text-slate-800">{{ $activity->start_time?->format('H:i') ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Deskripsi atau tujuan</dt>
                            <dd class="whitespace-pre-line font-medium text-slate-800">{{ $activity->description ?: '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Hasil kegiatan</dt>
                            <dd class="whitespace-pre-line font-medium text-slate-800">{{ $activity->handling_result ?: 'Belum dicatat' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="font-headline text-lg font-bold text-slate-800">Dokumentasi</h2>
                        <span class="text-xs font-semibold text-slate-500">{{ $activity->attachments->count() }} file</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse ($activity->attachments as $attachment)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-sidebar-active shadow-sm">
                                    <span class="material-symbols-outlined">image</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-700" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $attachment->mime_type }} · {{ \App\Support\FileSize::format($attachment->file_size) }}</p>
                                </div>
                                <a href="{{ route('admin.kegiatan.attachments.show', [$activity, $attachment]) }}" target="_blank" rel="noopener" title="Pratinjau dokumentasi" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                                <form action="{{ route('admin.kegiatan.attachments.destroy', [$activity, $attachment]) }}" method="POST" data-confirm-delete="Hapus dokumentasi ini secara permanen?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus dokumentasi" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400 sm:col-span-2">Belum ada dokumentasi kegiatan.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 font-headline text-lg font-bold text-slate-800">Lokasi</h2>
                    <p class="whitespace-pre-line text-sm font-medium text-slate-700">{{ $activity->location_address }}</p>
                    <div id="activity-detail-map" class="mt-4 h-64 overflow-hidden rounded-xl border border-slate-200 bg-slate-100" aria-label="Peta lokasi kegiatan"></div>
                    <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                        <p>Latitude: {{ $activity->latitude }}</p>
                        <p class="mt-1">Longitude: {{ $activity->longitude }}</p>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 font-headline text-lg font-bold text-slate-800">Petugas</h2>
                    <div class="space-y-3">
                        @foreach ($activity->officers as $officer)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p class="text-sm font-semibold text-slate-700">{{ $officer->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $officer->employee_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $officer->rank ?: '-' }}{{ $officer->position ? ' · '.$officer->position : '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        if (window.L && document.getElementById('activity-detail-map')) {
            const latitude = Number.parseFloat(@json($activity->latitude));
            const longitude = Number.parseFloat(@json($activity->longitude));
            const map = L.map('activity-detail-map', { scrollWheelZoom: false });

            if (window.KulonProgoMap) {
                window.KulonProgoMap.setup(map);
            } else {
                map.setView([-7.75, 110.16], 11);
            }

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
                map.setView([latitude, longitude], 16);
                L.marker([latitude, longitude]).addTo(map);
            }
        }
    </script>
@endpush
