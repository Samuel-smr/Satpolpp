@extends('layouts.admin')

@section('title', 'Aset Operasional')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Inventaris Aset Operasional</h1>
            <p class="mt-1 text-sm text-slate-500">Daftar sarana, prasarana, kendaraan dinas, dan perlengkapan Satpol PP.</p>
        </div>

        <button type="button" onclick="bukaForm()"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidebar-active px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-sidebar-hover">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Aset
        </button>
    </div>

    <form method="GET" action="{{ route('admin.aset') }}" class="mb-6 grid grid-cols-1 items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="asset-search">PENCARIAN</label><input id="asset-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, kode, atau lokasi" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="asset-filter-category">KATEGORI</label><select id="asset-filter-category" name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Semua kategori</option>@foreach ($assetCategories as $category)<option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>{{ $category->label() }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="asset-filter-condition">KONDISI</label><select id="asset-filter-condition" name="condition" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Semua kondisi</option>@foreach ($assetConditions as $condition)<option value="{{ $condition->value }}" @selected(($filters['condition'] ?? '') === $condition->value)>{{ $condition->label() }}</option>@endforeach</select></div>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-sidebar-active px-4 py-2 text-sm font-semibold text-white">Terapkan</button><a href="{{ route('admin.aset') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm">Reset</a></div>
    </form>

    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Aset', 'value' => $assetSummary['total'], 'icon' => 'inventory_2', 'class' => 'bg-orange-100/70 text-orange-600'],
            ['label' => 'Kondisi Baik', 'value' => $assetSummary['good'], 'icon' => 'check_circle', 'class' => 'bg-emerald-100/70 text-emerald-600'],
            ['label' => 'Perlu Perbaikan', 'value' => $assetSummary['needs_attention'], 'icon' => 'build', 'class' => 'bg-amber-100/70 text-amber-600'],
            ['label' => 'Kendaraan Dinas', 'value' => $assetSummary['vehicles'], 'icon' => 'directions_car', 'class' => 'bg-blue-100/70 text-brand-600'],
        ] as $stat)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold leading-snug text-slate-600">{{ $stat['label'] }}</p>
                        <p class="font-headline mt-1 text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $stat['class'] }}">
                        <span class="material-symbols-outlined text-[22px]">{{ $stat['icon'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="text-sm font-semibold text-slate-700">Daftar Aset</span>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Data Database</span>
        </div>

        @if ($assets->isEmpty())
            <div class="px-6 py-12 text-center text-slate-400">
                <span class="material-symbols-outlined mb-2 text-4xl">inventory_2</span>
                <p>Belum ada data aset.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-700">
                            <th class="px-6 py-3.5 text-left font-semibold">Nama Aset</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Kategori</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Kondisi</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Lokasi</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Tahun</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Jatuh Tempo Pajak</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Servis Oli</th>
                            <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-slate-800">{{ $asset->name }}</p>
                                    @if ($asset->asset_code)
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $asset->asset_code }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $asset->category->label() }}</td>
                                <td class="px-6 py-3.5">
                                    <span @class([
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $asset->condition === \App\AssetCondition::Good,
                                        'border-amber-200 bg-amber-50 text-amber-700' => $asset->condition === \App\AssetCondition::NeedsRepair,
                                        'border-red-200 bg-red-50 text-red-700' => $asset->condition === \App\AssetCondition::SeverelyDamaged,
                                    ])>{{ $asset->condition->label() }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $asset->location ?: '-' }}</td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $asset->acquisition_year ?: '-' }}</td>
                                <td class="px-6 py-3.5 text-slate-500">
                                    @if ($asset->category === \App\AssetCategory::Vehicle && $asset->tax_due_date)
                                        {{ $asset->tax_due_date->format('d/m/Y') }}
                                        @if ($asset->tax_due_date->isPast() && !$asset->tax_due_date->isToday())
                                            <span class="ml-1 inline-block rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">LEWAT</span>
                                        @elseif ($asset->tax_due_date->isToday())
                                            <span class="ml-1 inline-block rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">HARI INI</span>
                                        @elseif (today()->diffInDays($asset->tax_due_date) <= 30)
                                            <span class="ml-1 inline-block rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">
                                                {{ (int) today()->diffInDays($asset->tax_due_date) }} hari
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-slate-500">
                                    {{ $asset->category === \App\AssetCategory::Vehicle && $asset->next_oil_service_km !== null ? number_format($asset->next_oil_service_km, 0, ',', '.').' km' : '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            onclick="bukaEditAset(@js([
                                                'id' => $asset->id,
                                                'asset_code' => $asset->asset_code,
                                                'name' => $asset->name,
                                                'category' => $asset->category->value,
                                                'condition' => $asset->condition->value,
                                                'location' => $asset->location,
                                                'acquisition_year' => $asset->acquisition_year,
                                                'tax_due_date' => $asset->tax_due_date?->toDateString(),
                                                'next_oil_service_km' => $asset->next_oil_service_km,
                                            ]))"
                                            class="rounded-lg p-1.5 text-slate-400 hover:bg-amber-50 hover:text-amber-600"
                                            title="Edit aset">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.aset.destroy', $asset) }}"
                                            data-confirm-delete="Apakah Anda yakin ingin menghapus aset &quot;{{ $asset->name }}&quot;? Aset akan disembunyikan dari daftar, tetapi tetap tersimpan di database.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($assets->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $assets->links() }}</div>
            @endif
        @endif
    </div>

    <div id="modal-form" @class(['fixed inset-0 z-50 flex items-center justify-center p-4', 'hidden' => !$errors->any()])>
        <div class="absolute inset-0 bg-black/40" onclick="tutupForm()"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 id="asset-modal-title" class="font-headline font-bold text-slate-800">Tambah Aset</h2>
                <button type="button" onclick="tutupForm()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="aset-form" method="POST" action="{{ route('admin.aset.store') }}" class="space-y-4 p-6">
                @csrf
                <input id="asset-method" type="hidden" name="_method" value="POST">
                <input id="asset-context-id" type="hidden" name="asset_context_id" value="{{ old('asset_context_id') }}">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="asset-name" class="mb-1 block text-sm font-medium">Nama Aset <span class="text-red-500">*</span></label>
                        <input id="asset-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                            placeholder="Contoh: Mobil Patroli"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="asset-code" class="mb-1 block text-sm font-medium">Kode Aset</label>
                        <input id="asset-code" type="text" name="asset_code" value="{{ old('asset_code') }}" maxlength="50"
                            placeholder="Contoh: AST-001"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        @error('asset_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="asset-location" class="mb-1 block text-sm font-medium">Lokasi</label>
                        <input id="asset-location" type="text" name="location" value="{{ old('location') }}" maxlength="255"
                            placeholder="Contoh: Kantor Satpol PP"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div>
                        <label for="asset-category" class="mb-1 block text-sm font-medium">Kategori <span class="text-red-500">*</span></label>
                        <select id="asset-category" name="category" required onchange="toggleKendaraanFields()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih Kategori</option>
                            @foreach ($assetCategories as $category)
                                <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="asset-condition" class="mb-1 block text-sm font-medium">Kondisi <span class="text-red-500">*</span></label>
                        <select id="asset-condition" name="condition" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih Kondisi</option>
                            @foreach ($assetConditions as $condition)
                                <option value="{{ $condition->value }}" @selected(old('condition') === $condition->value)>{{ $condition->label() }}</option>
                            @endforeach
                        </select>
                        @error('condition')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="acquisition-year" class="mb-1 block text-sm font-medium">Tahun Perolehan</label>
                        <input id="acquisition-year" type="number" name="acquisition_year" value="{{ old('acquisition_year') }}"
                            min="1900" max="{{ now()->year }}" placeholder="Contoh: 2022"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        @error('acquisition_year')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div id="kendaraan-fields" class="hidden grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tax-due-date" class="mb-1 block text-sm font-medium">Jatuh Tempo Pajak</label>
                        <input id="tax-due-date" type="date" name="tax_due_date" value="{{ old('tax_due_date') }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        @error('tax_due_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="next-oil-service" class="mb-1 block text-sm font-medium">Servis Oli Berikutnya (km)</label>
                        <input id="next-oil-service" type="number" name="next_oil_service_km" value="{{ old('next_oil_service_km') }}"
                            min="0" step="1" placeholder="Contoh: 10000"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        @error('next_oil_service_km')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupForm()" class="rounded-xl border border-slate-300 px-5 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-sidebar-active px-6 py-2 font-medium text-white transition-colors hover:bg-sidebar-hover">
                        <span id="asset-submit-label">Simpan Aset</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalAset = document.getElementById('modal-form');
        const formAset = document.getElementById('aset-form');
        const kategoriAset = document.getElementById('asset-category');
        const fieldKendaraan = document.getElementById('kendaraan-fields');
        const urlTambahAset = @json(route('admin.aset.store'));
        const polaUrlEditAset = @json(route('admin.aset.update', ['asset' => '__ASSET__']));

        function bukaForm() {
            formAset.reset();
            formAset.action = urlTambahAset;
            document.getElementById('asset-method').value = 'POST';
            document.getElementById('asset-context-id').value = '';
            document.getElementById('asset-modal-title').textContent = 'Tambah Aset';
            document.getElementById('asset-submit-label').textContent = 'Simpan Aset';
            toggleKendaraanFields();
            modalAset.classList.remove('hidden');
        }

        function bukaEditAset(asset) {
            formAset.reset();
            formAset.action = polaUrlEditAset.replace('__ASSET__', asset.id);
            document.getElementById('asset-method').value = 'PUT';
            document.getElementById('asset-context-id').value = asset.id;
            formAset.elements.asset_code.value = asset.asset_code ?? '';
            formAset.elements.name.value = asset.name;
            formAset.elements.category.value = asset.category;
            formAset.elements.condition.value = asset.condition;
            formAset.elements.location.value = asset.location ?? '';
            formAset.elements.acquisition_year.value = asset.acquisition_year ?? '';
            formAset.elements.tax_due_date.value = asset.tax_due_date ?? '';
            formAset.elements.next_oil_service_km.value = asset.next_oil_service_km ?? '';
            document.getElementById('asset-modal-title').textContent = 'Edit Aset';
            document.getElementById('asset-submit-label').textContent = 'Simpan Perubahan';
            toggleKendaraanFields();
            modalAset.classList.remove('hidden');
        }

        function tutupForm() {
            modalAset.classList.add('hidden');
        }

        function toggleKendaraanFields() {
            const kendaraanDipilih = kategoriAset.value === @json(\App\AssetCategory::Vehicle->value);
            fieldKendaraan.classList.toggle('hidden', !kendaraanDipilih);
            fieldKendaraan.classList.toggle('grid', kendaraanDipilih);

            if (!kendaraanDipilih) {
                formAset.elements.tax_due_date.value = '';
                formAset.elements.next_oil_service_km.value = '';
            }
        }

        toggleKendaraanFields();

        @if ($editingAsset)
            bukaEditAset(@js([
                'id' => $editingAsset->id,
                'asset_code' => old('asset_code', $editingAsset->asset_code),
                'name' => old('name', $editingAsset->name),
                'category' => old('category', $editingAsset->category->value),
                'condition' => old('condition', $editingAsset->condition->value),
                'location' => old('location', $editingAsset->location),
                'acquisition_year' => old('acquisition_year', $editingAsset->acquisition_year),
                'tax_due_date' => old('tax_due_date', $editingAsset->tax_due_date?->toDateString()),
                'next_oil_service_km' => old('next_oil_service_km', $editingAsset->next_oil_service_km),
            ]));
        @endif
    </script>
@endpush
