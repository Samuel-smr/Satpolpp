@extends($activityLayout ?? 'layouts.admin')

@section('title', isset($activity) ? 'Edit Kegiatan Internal' : 'Tambah Kegiatan Internal')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@section('content')
    @php
        $editingActivity = $activity ?? null;
        $isEditing = $editingActivity !== null;
        $defaultOfficerIds = $isEditing ? $editingActivity->officers->pluck('id')->all() : [];
        $selectedOfficerIds = array_map('strval', old('officer_ids', $defaultOfficerIds));
        $existingDocumentationCount = $isEditing ? $editingActivity->attachments->count() : 0;
        $activityRoutePrefix = $activityRoutePrefix ?? 'admin.kegiatan';
        $closeRoute = route($activityRoutePrefix);
    @endphp

    <div class="fixed inset-y-0 left-0 right-0 lg:left-64 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="activity-modal-title">
        <a href="{{ $closeRoute }}" class="absolute inset-0 bg-black/40" aria-label="Tutup formulir"></a>

        <div class="relative flex max-h-[92dvh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
            <div>
                    <h1 id="activity-modal-title" class="font-headline text-xl font-bold text-slate-900">{{ $isEditing ? 'Edit Kegiatan Internal' : 'Tambah Kegiatan Internal' }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $isEditing ? 'Perbarui agenda, hasil, petugas, lokasi, atau tambahkan dokumentasi.' : 'Catat agenda operasional yang tidak berasal dari laporan masyarakat.' }}</p>
            </div>
                <a href="{{ $closeRoute }}" aria-label="Tutup formulir" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </a>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto p-6">

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $isEditing ? route($activityRoutePrefix.'.update', $editingActivity) : route($activityRoutePrefix.'.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if ($isEditing)
                @method('PUT')
                <input type="hidden" name="current_updated_at" value="{{ $editingActivity->updated_at->format('Y-m-d H:i:s') }}">
            @endif

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Informasi Kegiatan</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="activity_template_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Template kegiatan (opsional)</label>
                        <select id="activity_template_id" name="activity_template_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Tanpa template</option>
                            @foreach ($activityTemplates as $template)
                                <option value="{{ $template->id }}" data-name="{{ $template->name }}" data-category="{{ $template->name }}" data-description="{{ $template->default_description }}" @selected((string) old('activity_template_id', $editingActivity?->activity_template_id) === (string) $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="activity_category" class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori kegiatan</label>
                        <select id="activity_category" name="category" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih kategori</option>
                            @foreach ($activityTemplates as $template)
                                <option value="{{ $template->name }}" @selected(old('category', $editingActivity?->category) === $template->name)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-400">Kategori mengikuti template yang dipilih dan tetap dapat diubah manual.</p>
                        @error('category')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="activity_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama kegiatan</label>
                        <input id="activity_name" name="name" type="text" maxlength="255" required value="{{ old('name', $editingActivity?->name) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div>
                        <label for="activity_date" class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal pelaksanaan</label>
                        <input id="activity_date" name="activity_date" type="date" required value="{{ old('activity_date', $editingActivity?->activity_date?->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div>
                        <label for="start_time" class="mb-1.5 block text-sm font-semibold text-slate-700">Waktu mulai (opsional)</label>
                        <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $editingActivity?->start_time?->format('H:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="activity_description" class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi atau tujuan (opsional)</label>
                        <textarea id="activity_description" name="description" rows="4" maxlength="5000" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('description', $editingActivity?->description) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="handling_result" class="mb-1.5 block text-sm font-semibold text-slate-700">Hasil kegiatan (opsional)</label>
                        <textarea id="handling_result" name="handling_result" rows="3" maxlength="5000" placeholder="Dapat diisi jika kegiatan sudah dilaksanakan." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">{{ old('handling_result', $editingActivity?->handling_result) }}</textarea>
                    </div>
                </div>
            </section>

            <x-activity-findings-fields
                prefix="activity"
                :findings="old('findings', $editingActivity?->findings->map(fn ($finding) => ['type' => $finding->type->value, 'quantity' => $finding->quantity, 'unit' => $finding->unit->value])->all() ?? [])"
            />

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Lokasi Kegiatan</h2>
                <div class="space-y-5">
                    <div>
                        <label for="location_address" class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat lengkap</label>
                        <input id="location_address" name="location_address" type="text" maxlength="2000" required value="{{ old('location_address', $editingActivity?->location_address) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div>
                        <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                            <label class="text-sm font-semibold text-slate-700">Titik koordinat</label>
                            <p class="text-xs text-slate-400">Cari lokasi, gunakan lokasi saat ini, atau klik peta.</p>
                        </div>
                        <x-location-map-controls prefix="activity" />
                        <div id="activity-map" class="h-72 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="latitude" class="mb-1.5 block text-sm font-semibold text-slate-700">Latitude</label>
                            <input id="latitude" name="latitude" type="number" min="-90" max="90" step="0.0000001" required value="{{ old('latitude', $editingActivity?->latitude) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                        </div>
                        <div>
                            <label for="longitude" class="mb-1.5 block text-sm font-semibold text-slate-700">Longitude</label>
                            <input id="longitude" name="longitude" type="number" min="-180" max="180" step="0.0000001" required value="{{ old('longitude', $editingActivity?->longitude) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-sidebar-active focus:bg-white focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="mb-5 font-headline text-lg font-bold text-slate-800">Petugas dan Dokumentasi</h2>
                <fieldset>
                    <legend class="mb-3 text-sm font-semibold text-slate-700">Petugas yang mengikuti</legend>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <label for="officer-search" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Daftar petugas aktif</label>
                            <div class="relative mb-3">
                                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
                                <input id="officer-search" type="search" placeholder="Cari nama atau nomor petugas..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm outline-none transition focus:border-sidebar-active focus:ring-2 focus:ring-sidebar-active/20">
                            </div>
                            <div id="officer-list" class="max-h-64 space-y-2 overflow-y-auto pr-1">
                                @forelse ($activeOfficers as $officer)
                                    <label data-officer-item data-search-text="{{ strtolower($officer->name.' '.$officer->employee_number.' '.$officer->position) }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition-colors hover:border-sidebar-active/40 hover:bg-blue-50/40">
                                        <input type="checkbox" name="officer_ids[]" value="{{ $officer->id }}" @checked(in_array((string) $officer->id, $selectedOfficerIds, true)) data-officer-checkbox data-name="{{ $officer->name }}" data-employee-number="{{ $officer->employee_number }}" data-position="{{ $officer->position }}" class="mt-1 rounded border-slate-300 text-sidebar-active focus:ring-sidebar-active">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-slate-700">{{ $officer->name }}</span>
                                            <span class="block truncate text-xs text-slate-400">{{ $officer->employee_number }}{{ $officer->position ? ' · '.$officer->position : '' }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">Belum ada petugas aktif. Kegiatan belum dapat dibuat.</p>
                                @endforelse
                            </div>
                            <p id="officer-search-empty" class="hidden rounded-xl border border-dashed border-slate-200 bg-white p-4 text-center text-sm text-slate-400">Petugas tidak ditemukan.</p>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-blue-700">Petugas terpilih</h3>
                                <span id="selected-officer-count" class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-blue-600">0</span>
                            </div>
                            <div id="selected-officer-list" class="max-h-64 space-y-2 overflow-y-auto pr-1">
                                <p id="selected-officer-empty" class="rounded-xl border border-dashed border-blue-200 bg-white/70 p-4 text-center text-sm text-blue-400">Belum ada petugas dipilih.</p>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mt-5">
                    <label for="documentation" class="mb-1.5 block text-sm font-semibold text-slate-700">Foto dokumentasi (opsional)</label>
                    @if ($isEditing && $editingActivity->attachments->isNotEmpty())
                        <div class="mb-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($editingActivity->attachments as $attachment)
                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium text-slate-700">{{ $attachment->original_name }}</p>
                                        <p class="text-xs text-slate-400">Dokumentasi tersimpan</p>
                                    </div>
                                    <a href="{{ route($activityRoutePrefix.'.attachments.show', [$editingActivity, $attachment]) }}" target="_blank" rel="noopener" title="Pratinjau dokumentasi" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                        <span class="material-symbols-outlined text-[19px]">visibility</span>
                                    </a>
                                    <button type="submit" form="delete-attachment-{{ $attachment->id }}" title="Hapus dokumentasi" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                        <span class="material-symbols-outlined text-[19px]">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <input id="documentation" name="documentation[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple data-maximum-files="{{ 3 - $existingDocumentationCount }}" @disabled($existingDocumentationCount >= 3) class="block w-full rounded-xl border border-slate-200 p-2 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold file:text-slate-700 disabled:cursor-not-allowed disabled:bg-slate-100">
                    <div id="documentation-preview" class="mt-3 hidden grid gap-3 sm:grid-cols-3"></div>
                    <p class="mt-1.5 text-xs text-slate-400">
                        @if ($isEditing)
                            Tersimpan {{ $existingDocumentationCount }} dari 3 foto. Anda dapat menambahkan maksimal {{ 3 - $existingDocumentationCount }} foto lagi.
                        @else
                            Maksimal 3 foto JPG/JPEG/PNG/WEBP, 5 MB per foto.
                        @endif
                        Dokumentasi disimpan untuk akses internal.
                    </p>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ $closeRoute }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" @disabled($activeOfficers->isEmpty()) class="rounded-xl bg-sidebar-active px-6 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-sidebar-hover disabled:cursor-not-allowed disabled:bg-slate-300">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Kegiatan Internal' }}</button>
            </div>
        </form>

        @if ($isEditing)
            @foreach ($editingActivity->attachments as $attachment)
                <form id="delete-attachment-{{ $attachment->id }}" action="{{ route($activityRoutePrefix.'.attachments.destroy', [$editingActivity, $attachment]) }}" method="POST" data-confirm-delete="Hapus dokumentasi ini secara permanen?">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="return_to" value="edit">
                </form>
            @endforeach
        @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        const officerSearch = document.getElementById('officer-search');
        const officerItems = Array.from(document.querySelectorAll('[data-officer-item]'));
        const officerCheckboxes = Array.from(document.querySelectorAll('[data-officer-checkbox]'));
        const selectedOfficerList = document.getElementById('selected-officer-list');
        const selectedOfficerCount = document.getElementById('selected-officer-count');
        const selectedOfficerEmpty = document.getElementById('selected-officer-empty');
        const officerSearchEmpty = document.getElementById('officer-search-empty');

        const renderSelectedOfficers = () => {
            if (!selectedOfficerList || !selectedOfficerCount) {
                return;
            }

            selectedOfficerList.querySelectorAll('[data-selected-officer]').forEach((item) => item.remove());
            const selected = officerCheckboxes.filter((checkbox) => checkbox.checked);
            selectedOfficerCount.textContent = selected.length;
            selectedOfficerEmpty?.classList.toggle('hidden', selected.length > 0);

            selected.forEach((checkbox) => {
                const item = document.createElement('div');
                item.dataset.selectedOfficer = 'true';
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
                    renderSelectedOfficers();
                });
                item.appendChild(removeButton);
                selectedOfficerList.appendChild(item);
            });
        };

        officerCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', renderSelectedOfficers));

        officerSearch?.addEventListener('input', () => {
            const query = officerSearch.value.trim().toLowerCase();
            let visibleCount = 0;

            officerItems.forEach((item) => {
                const isVisible = item.dataset.searchText.includes(query);
                item.classList.toggle('hidden', !isVisible);
                visibleCount += isVisible ? 1 : 0;
            });

            officerSearchEmpty?.classList.toggle('hidden', visibleCount > 0);
        });

        renderSelectedOfficers();

        const documentationInput = document.getElementById('documentation');
        const documentationPreview = document.getElementById('documentation-preview');
        let documentationPreviewUrls = [];

        const clearDocumentationPreview = () => {
            documentationPreviewUrls.forEach((url) => URL.revokeObjectURL(url));
            documentationPreviewUrls = [];
            documentationPreview.replaceChildren();
            documentationPreview.classList.add('hidden');
        };

        documentationInput?.addEventListener('change', () => {
            clearDocumentationPreview();
            const files = Array.from(documentationInput.files);
            const maximumFiles = Number.parseInt(documentationInput.dataset.maximumFiles, 10);

            if (files.length > maximumFiles) {
                documentationInput.value = '';
                window.tampilToast?.(`Anda hanya dapat menambahkan ${maximumFiles} foto lagi.`, 'warning');

                return;
            }

            files.forEach((file, index) => {
                const previewUrl = URL.createObjectURL(file);
                documentationPreviewUrls.push(previewUrl);

                const card = document.createElement('div');
                card.className = 'relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50';

                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = `Preview ${file.name}`;
                image.className = 'aspect-video w-full object-cover';
                card.appendChild(image);

                const name = document.createElement('p');
                name.className = 'truncate px-3 py-2 text-xs font-medium text-slate-600';
                name.textContent = file.name;
                card.appendChild(name);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.title = 'Hapus foto terpilih';
                removeButton.className = 'absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-red-600 shadow transition-colors hover:bg-red-50';
                removeButton.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
                removeButton.addEventListener('click', () => {
                    const remainingFiles = new DataTransfer();
                    Array.from(documentationInput.files).forEach((selectedFile, selectedIndex) => {
                        if (selectedIndex !== index) {
                            remainingFiles.items.add(selectedFile);
                        }
                    });
                    documentationInput.files = remainingFiles.files;
                    documentationInput.dispatchEvent(new Event('change'));
                });
                card.appendChild(removeButton);
                documentationPreview.appendChild(card);
            });

            documentationPreview.classList.toggle('hidden', files.length === 0);
        });

        const templateSelect = document.getElementById('activity_template_id');
        templateSelect?.addEventListener('change', function () {
            const selectedTemplate = this.options[this.selectedIndex];

            if (!selectedTemplate.value) {
                return;
            }

            document.getElementById('activity_name').value = selectedTemplate.dataset.name || '';
            document.getElementById('activity_category').value = selectedTemplate.dataset.category || '';
            document.getElementById('activity_description').value = selectedTemplate.dataset.description || '';
        });

        if (window.L && document.getElementById('activity-map')) {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const map = L.map('activity-map');

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
                searchInput: document.getElementById('activity-location-search'),
                searchButton: document.getElementById('activity-location-search-button'),
                currentLocationButton: document.getElementById('activity-current-location-button'),
                statusElement: document.getElementById('activity-location-status')
            });
        }
    </script>
@endpush
