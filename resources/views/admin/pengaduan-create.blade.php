@extends('layouts.admin')

@section('title', 'Tambah Laporan')

@section('content')
    <div class="fixed inset-y-0 left-0 right-0 lg:left-64 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
        <a href="{{ route('admin.pengaduan') }}" class="absolute inset-0 bg-black/40" aria-label="Tutup formulir"></a>
        <div class="relative flex max-h-[92dvh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                <div>
                    <h1 id="report-modal-title" class="font-headline text-xl font-bold text-slate-900">Tambah Laporan</h1>
                    <p class="mt-1 text-sm text-slate-500">Catat laporan dari kanal offline maupun online.</p>
                </div>
                <a href="{{ route('admin.pengaduan') }}" aria-label="Tutup formulir" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </a>
            </div>
            <div class="flex-1 space-y-6 overflow-y-auto p-6">

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <p class="font-semibold">Periksa kembali data laporan:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pengaduan.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 gap-6">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Informasi Laporan</h2>
                <div class="mt-5 grid grid-cols-1 gap-4">
                    <label class="block text-sm font-semibold text-slate-700">Sumber laporan
                        <select id="source" name="source" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            @foreach (\App\ReportSource::cases() as $source)<option value="{{ $source->value }}" @selected(old('source', 'offline') === $source->value)>{{ $source->label() }}</option>@endforeach
                        </select>
                    </label>
                    <label id="platform-field" class="block text-sm font-semibold text-slate-700">Platform online
                        <select name="source_platform" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih platform</option>
                            @foreach (\App\ReportSourcePlatform::cases() as $platform)<option value="{{ $platform->value }}" @selected(old('source_platform') === $platform->value)>{{ $platform->label() }}</option>@endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">Nama pelapor (opsional)
                        <input name="reporter_name" value="{{ old('reporter_name') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">Nomor HP/telepon
                        <input type="tel" name="reporter_phone" value="{{ old('reporter_phone') }}" required inputmode="tel" maxlength="20" pattern="\+?[0-9][0-9\s-]{8,18}" title="Masukkan 9–19 digit; boleh diawali + serta menggunakan spasi atau tanda hubung." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">Deskripsi laporan
                        <textarea name="description" required rows="5" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('description') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Lokasi dan Bukti</h2>
                <label class="block text-sm font-semibold text-slate-700">Alamat lokasi
                    <textarea id="location_address" name="location_address" required rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('location_address') }}</textarea>
                </label>
                <div class="mt-4">
                    <x-location-map-controls prefix="report" />
                </div>
                <div id="report-map" class="mt-4 h-64 rounded-xl border border-slate-200"></div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block text-sm font-semibold text-slate-700">Latitude<input id="latitude" name="latitude" value="{{ old('latitude', '-7.75') }}" required readonly class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900"></label>
                    <label class="block text-sm font-semibold text-slate-700">Longitude<input id="longitude" name="longitude" value="{{ old('longitude', '110.16') }}" required readonly class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900"></label>
                </div>
                <label class="mt-5 block text-sm font-medium text-slate-700">Foto bukti (maksimal 3)
                    <input type="file" name="evidence[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple class="mt-1.5 block w-full rounded-xl border border-slate-200 p-2 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold file:text-slate-700">
                </label>
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.pengaduan') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Batal</a>
                    <button class="rounded-lg bg-sidebar-active px-5 py-2.5 text-sm font-semibold text-white hover:bg-sidebar-hover">Simpan Laporan</button>
                </div>
            </section>
        </div>
    </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const source = document.getElementById('source');
        const platformField = document.getElementById('platform-field');
        function updateSourceFields() {
            const online = source.value === 'online';
            platformField.classList.toggle('hidden', !online);
            platformField.querySelector('select').required = online;
        }
        source.addEventListener('change', updateSourceFields);
        updateSourceFields();

        if (window.L && document.getElementById('report-map')) {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const map = L.map('report-map');

            if (window.KulonProgoMap) {
                window.KulonProgoMap.setup(map);
            } else {
                map.setView([-7.75, 110.16], 11);
            }

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            window.KulonProgoMap?.createLocationPicker({
                map,
                latitudeInput,
                longitudeInput,
                addressInput: document.getElementById('location_address'),
                searchInput: document.getElementById('report-location-search'),
                searchButton: document.getElementById('report-location-search-button'),
                currentLocationButton: document.getElementById('report-current-location-button'),
                statusElement: document.getElementById('report-location-status')
            });
        }
    </script>
@endpush
