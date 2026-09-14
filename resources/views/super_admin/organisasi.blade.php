@extends('layouts.super_admin')

@section('title', 'Manajemen Organisasi')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Struktur Organisasi</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola divisi, unit kerja, dan pembagian zona operasi Satpol PP.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-super-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Unit
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-super-active/10 text-super-active flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">account_balance</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Unit</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">account_tree</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Divisi / Seksi</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['divisions'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">groups</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Anggota</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['officers'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">map</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Zona Operasi</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['zones'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Unit Kerja</span>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">Database</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Nama Unit</th>
                        <th class="px-6 py-3 font-semibold">Jenis</th>
                        <th class="px-6 py-3 font-semibold">Pimpinan</th>
                        <th class="px-6 py-3 font-semibold">Zona / Wilayah</th>
                        <th class="px-6 py-3 font-semibold">Anggota</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3.5 font-medium text-slate-800">{{ $unit->name }}</td>
                            <td class="px-6 py-3.5"><span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $unit->type === \App\OrganizationalUnitType::Division ? 'border-purple-200 bg-purple-50 text-purple-700' : ($unit->type === \App\OrganizationalUnitType::Team ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700') }}">{{ $unit->type->label() }}</span></td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $unit->leader?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $unit->operationalZone?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 font-semibold text-slate-700">{{ $unit->officers_count }}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <button type="button" onclick="bukaEdit(@js($unit))" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg" title="Edit"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                                <button type="button" onclick="hapusUnit({{ $unit->id }}, @js($unit->name))" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Hapus"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Belum ada unit kerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal tambah/ubah unit --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800" id="modal-judul">Tambah Unit</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="unit-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanUnit(event)">
                @csrf
                <input type="hidden" name="edit_index" value="">
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="is_active" value="1">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Unit <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Seksi Ketertiban Umum"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Jenis <span class="text-red-500">*</span></label>
                        <select name="type" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                            <option value="">Pilih Jenis</option>
                            @foreach ($unitTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pimpinan / Kepala</label>
                        <select name="leader_officer_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                            <option value="">Belum ditentukan</option>
                            @foreach ($leaders as $leader)<option value="{{ $leader->id }}">{{ $leader->name }} — {{ $leader->employee_number }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Zona / Wilayah Operasi</label>
                    <select name="operational_zone_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                        <option value="">Tanpa zona</option>
                        @foreach ($zones as $zone)<option value="{{ $zone->id }}">{{ $zone->name }}</option>@endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kode Unit (opsional)</label>
                    <input type="text" name="code" maxlength="50" placeholder="Contoh: REGU-A"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Unit Induk (opsional)</label>
                    <select name="parent_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                        <option value="">Tanpa unit induk</option>
                        @foreach ($units as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" maxlength="2000" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupModal()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-super-active text-white px-6 py-2 rounded-xl font-medium hover:bg-brand-600 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>



@endsection

@push('scripts')
{{-- Kode prototipe daftar lokal dinonaktifkan; CRUD menggunakan endpoint Laravel pada skrip setelah blok ini.
    let editIndex = null;

    function getUnits() {
        return [];
    }

    function saveAll() {}

    function jenisClass(j) {
        if (j === 'Divisi') return 'bg-purple-50 text-purple-700 border-purple-200';
        if (j === 'Regu') return 'bg-blue-50 text-blue-700 border-blue-200';
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    }

    function renderList() {
        const data = getUnits();
        document.getElementById('stat-total').textContent = data.length;
        document.getElementById('stat-divisi').textContent = data.filter(u => u.jenis === 'Divisi').length;
        document.getElementById('stat-anggota').textContent = data.reduce((s, u) => s + (Number(u.anggota) || 0), 0);
        document.getElementById('stat-zona').textContent = new Set(data.map(u => u.zona).filter(Boolean)).size;

        const tbody = document.getElementById('unit-list');
        const kosong = document.getElementById('kosong');

        if (data.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = data.map((u, i) => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-6 py-3.5 font-medium text-slate-800">${u.nama}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border ${jenisClass(u.jenis)}">${u.jenis}</span>
                </td>
                <td class="px-6 py-3.5 text-slate-500">${u.pimpinan || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${u.zona || '-'}</td>
                <td class="px-6 py-3.5 font-semibold text-slate-700">${u.anggota || 0}</td>
                <td class="px-6 py-3.5 text-right whitespace-nowrap">
                    <button type="button" onclick="bukaEdit(${i})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Edit">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </button>
                    <button type="button" onclick="hapusUnit(${i})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function bukaTambah() {
        editIndex = null;
        document.getElementById('modal-judul').textContent = 'Tambah Unit';
        document.getElementById('unit-form').reset();
        document.querySelector('[name=edit_index]').value = '';
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(i) {
        const u = getUnits()[i];
        editIndex = i;
        const f = document.getElementById('unit-form');
        f.reset();
        document.getElementById('modal-judul').textContent = 'Ubah Unit';
        document.querySelector('[name=edit_index]').value = i;
        f.nama.value = u.nama;
        f.jenis.value = u.jenis;
        f.pimpinan.value = u.pimpinan || '';
        f.zona.value = u.zona || '';
        f.anggota.value = u.anggota || '';
        document.getElementById('modal').classList.remove('hidden');
    }

    function tutupModal() { document.getElementById('modal').classList.add('hidden'); }

    function simpanUnit(e) {
        e.preventDefault();
        const f = document.getElementById('unit-form');
        const data = {
            nama: f.nama.value.trim(),
            jenis: f.jenis.value,
            pimpinan: f.pimpinan.value.trim(),
            zona: f.zona.value.trim(),
            anggota: f.anggota.value.trim(),
        };

        const all = getUnits();
        const idx = document.querySelector('[name=edit_index]').value;
        if (idx !== '') {
            all[idx] = data;
            tampilToast('Edit berhasil! Data unit organisasi telah diperbarui.');
        } else {
            all.unshift(data);
            tampilToast('Berhasil menambah data unit organisasi.');
        }
        saveAll(all);
        renderList();
        tutupModal();
    }

    function hapusUnit(i) {
        const all = getUnits();
        const nama = all[i] ? `unit "${all[i].nama}"` : 'unit ini';
        window.konfirmasiHapus({
            title: 'Hapus Unit Organisasi',
            message: `Apakah Anda yakin ingin menghapus ${nama}? Data yang dihapus tidak dapat dipulihkan.`,
            onConfirm: () => {
                all.splice(i, 1);
                saveAll(all);
                renderList();
                tampilToast('Unit berhasil dihapus.');
            }
        });
    }



    // Daftar dirender oleh Blade dari database.
--}}
<script>
    window.tutupModal = function () {
        document.getElementById('modal')?.classList.add('hidden');
    };

    function bukaTambah() {
        const form = document.getElementById('unit-form');
        form.reset();
        form.elements._method.value = 'POST';
        form.elements.edit_index.value = '';
        form.elements.is_active.value = '1';
        document.getElementById('modal-judul').textContent = 'Tambah Unit';
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(unit) {
        const form = document.getElementById('unit-form');
        form.reset();
        form.elements.edit_index.value = unit.id;
        form.elements._method.value = 'PUT';
        form.elements.name.value = unit.name || '';
        form.elements.type.value = unit.type || '';
        form.elements.leader_officer_id.value = unit.leader_officer_id || '';
        form.elements.operational_zone_id.value = unit.operational_zone_id || '';
        form.elements.code.value = unit.code || '';
        form.elements.parent_id.value = unit.parent_id || '';
        form.elements.description.value = unit.description || '';
        form.elements.is_active.value = unit.is_active ? '1' : '0';
        document.getElementById('modal-judul').textContent = 'Ubah Unit';
        document.getElementById('modal').classList.remove('hidden');
    }

    function simpanUnit(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const id = form.elements.edit_index.value;
        form.action = id === ''
            ? @json(route('super_admin.organisasi.store'))
            : @json(route('super_admin.organisasi.update', ['organizationalUnit' => '__UNIT__'])).replace('__UNIT__', id);
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.classList.add('cursor-wait', 'opacity-70');
        submitButton.textContent = 'Memproses...';
        form.submit();
    }

    function hapusUnit(id, nama) {
        window.konfirmasiHapus({
            title: 'Hapus Unit Organisasi',
            message: `Apakah Anda yakin ingin menghapus unit "${nama}"?`,
            onConfirm: () => {
                fetch(@json(route('super_admin.organisasi.destroy', ['organizationalUnit' => '__UNIT__'])).replace('__UNIT__', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(async response => {
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Unit gagal dihapus.');
                    window.tampilkanToastLaluMuatUlang(result.message || 'Unit organisasi berhasil dihapus.');
                }).catch(error => window.tampilToast(error.message, 'error'));
            },
        });
    }
</script>
@endpush
