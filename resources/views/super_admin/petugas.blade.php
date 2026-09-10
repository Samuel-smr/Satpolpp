@extends('layouts.super_admin')

@section('title', 'Manajemen Petugas')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Data Petugas</h1>
            <p class="text-sm text-slate-500 mt-1">Direktori seluruh personil Satpol PP, regu lapangan, dan nomor NIP.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-super-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            Tambah Petugas
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-super-active/10 text-super-active flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">badge</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Petugas</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">task_alt</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Aktif</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">beach_access</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Cuti</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['leave'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">group_off</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Nonaktif</p>
                    <p class="font-headline text-2xl font-bold text-slate-900">{{ $statistics['inactive'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Personil</span>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">Database</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Petugas</th>
                        <th class="px-6 py-3 font-semibold">NIP / KTA</th>
                        <th class="px-6 py-3 font-semibold">Pangkat / Jabatan</th>
                        <th class="px-6 py-3 font-semibold">Regu</th>
                        <th class="px-6 py-3 font-semibold">Zona</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officers as $officer)
                        @php
                            $statusClass = match ($officer->status) {
                                \App\OfficerStatus::Active => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                \App\OfficerStatus::Leave => 'bg-amber-50 text-amber-700 border-amber-200',
                                \App\OfficerStatus::Inactive => 'bg-red-50 text-red-700 border-red-200',
                            };
                        @endphp
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3.5"><div class="flex items-center gap-3"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-super-active/10 text-xs font-bold text-slate-700">{{ collect(explode(' ', $officer->name))->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->join('') }}</div><span class="font-medium text-slate-800">{{ $officer->name }}</span></div></td>
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-500">{{ $officer->employee_number }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ collect([$officer->rank, $officer->position])->filter()->join(' / ') ?: '-' }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $officer->organizationalUnit?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $officer->operationalZone?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5"><span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $officer->status->label() }}</span></td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <button type="button" onclick="toggleStatus({{ $officer->id }})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg" title="Ubah status"><span class="material-symbols-outlined text-[20px]">swap_vert</span></button>
                                <button type="button" onclick="bukaEdit(@js(['id' => $officer->id, 'name' => $officer->name, 'employee_number' => $officer->employee_number, 'rank' => $officer->rank, 'position' => $officer->position, 'organizational_unit_id' => $officer->organizational_unit_id, 'operational_zone_id' => $officer->operational_zone_id, 'email' => $officer->email, 'phone' => $officer->phone, 'address' => $officer->address, 'joined_at' => $officer->joined_at?->toDateString(), 'status' => $officer->status->value]))" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg" title="Edit"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                                <button type="button" onclick="hapusPetugas({{ $officer->id }}, @js($officer->name))" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Hapus"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Belum ada data petugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal tambah/ubah petugas --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800" id="modal-judul">Tambah Petugas</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="petugas-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanPetugas(event)">
                @csrf
                <input type="hidden" name="edit_index" value="">
                <input type="hidden" name="_method" value="POST">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Joko Widodo Samudra"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">NIP / KTA <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_number" required maxlength="50" placeholder="Contoh: PP-001"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pangkat</label>
                        <input type="text" name="rank" maxlength="100" placeholder="Contoh: Penata Muda"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Jabatan</label>
                        <input type="text" name="position" maxlength="150" placeholder="Contoh: Anggota"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Regu / Unit</label>
                        <select name="organizational_unit_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white"><option value="">Tanpa unit</option>@foreach ($units as $unit)<option value="{{ $unit->id }}">{{ $unit->name }}</option>@endforeach</select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Zona Operasi</label>
                    <select name="operational_zone_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white"><option value="">Tanpa zona</option>@foreach ($zones as $zone)<option value="{{ $zone->id }}">{{ $zone->name }}</option>@endforeach</select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none"></div>
                    <div><label class="block text-sm font-medium mb-1">Nomor HP</label><input type="tel" name="phone" inputmode="tel" maxlength="20" pattern="\+?[0-9][0-9\s-]{8,18}" title="Masukkan 9–19 digit; boleh diawali + serta menggunakan spasi atau tanda hubung." placeholder="Contoh: 081234567890" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium mb-1">Tanggal Bergabung</label><input type="date" name="joined_at" max="{{ now()->toDateString() }}" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none"></div>
                    <div><label class="block text-sm font-medium mb-1">Alamat</label><input type="text" name="address" maxlength="2000" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                        <option value="">Pilih Status</option>
                        @foreach ($statuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach
                    </select>
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
    function getPetugas() {
        return [];
    }

    function saveAll() {}

    function statusClass(s) {
        if (s === 'aktif') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        if (s === 'cuti') return 'bg-amber-50 text-amber-700 border-amber-200';
        return 'bg-red-50 text-red-700 border-red-200';
    }

    function renderList() {
        const data = getPetugas();
        document.getElementById('stat-total').textContent = data.length;
        document.getElementById('stat-aktif').textContent = data.filter(p => p.status === 'aktif').length;
        document.getElementById('stat-cuti').textContent = data.filter(p => p.status === 'cuti').length;
        document.getElementById('stat-nonaktif').textContent = data.filter(p => p.status === 'nonaktif').length;

        const tbody = document.getElementById('petugas-list');
        const kosong = document.getElementById('kosong');

        if (data.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = data.map((p, i) => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-super-active/10 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            ${p.nama.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
                        </div>
                        <span class="font-medium text-slate-800">${p.nama}</span>
                    </div>
                </td>
                <td class="px-6 py-3.5 text-slate-500 font-mono text-xs">${p.nip}</td>
                <td class="px-6 py-3.5 text-slate-500">${p.pangkat || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${p.regu || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${p.zona || '-'}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border capitalize ${statusClass(p.status)}">${p.status}</span>
                </td>
                <td class="px-6 py-3.5 text-right whitespace-nowrap">
                    <button type="button" onclick="toggleStatus(${i})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Ubah status">
                        <span class="material-symbols-outlined text-[20px]">swap_vert</span>
                    </button>
                    <button type="button" onclick="bukaEdit(${i})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Edit">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </button>
                    <button type="button" onclick="hapusPetugas(${i})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function bukaTambah() {
        document.getElementById('modal-judul').textContent = 'Tambah Petugas';
        document.getElementById('petugas-form').reset();
        document.querySelector('[name=edit_index]').value = '';
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(i) {
        const p = getPetugas()[i];
        const f = document.getElementById('petugas-form');
        f.reset();
        document.getElementById('modal-judul').textContent = 'Ubah Petugas';
        document.querySelector('[name=edit_index]').value = i;
        f.nama.value = p.nama;
        f.nip.value = p.nip;
        f.pangkat.value = p.pangkat || '';
        f.regu.value = p.regu || '';
        f.zona.value = p.zona || '';
        f.status.value = p.status;
        document.getElementById('modal').classList.remove('hidden');
    }

    function tutupModal() { document.getElementById('modal').classList.add('hidden'); }

    function simpanPetugas(e) {
        e.preventDefault();
        const f = document.getElementById('petugas-form');
        const data = {
            nama: f.nama.value.trim(),
            nip: f.nip.value.trim(),
            pangkat: f.pangkat.value.trim(),
            regu: f.regu.value.trim(),
            zona: f.zona.value.trim(),
            status: f.status.value,
        };

        const all = getPetugas();
        const idx = document.querySelector('[name=edit_index]').value;
        if (idx !== '') {
            all[idx] = data;
            tampilToast('Edit berhasil! Data petugas telah diperbarui.');
        } else {
            all.unshift(data);
            tampilToast('Berhasil menambah data petugas.');
        }
        saveAll(all);
        renderList();
        tutupModal();
    }

    function toggleStatus(i) {
        const all = getPetugas();
        const order = { 'aktif': 'cuti', 'cuti': 'nonaktif', 'nonaktif': 'aktif' };
        all[i].status = order[all[i].status] || 'aktif';
        saveAll(all);
        renderList();
        tampilToast('Edit berhasil! Status petugas diubah menjadi ' + all[i].status + '.');
    }

    function hapusPetugas(i) {
        const all = getPetugas();
        const nama = all[i] ? `petugas "${all[i].nama}"` : 'petugas ini';
        window.konfirmasiHapus({
            title: 'Hapus Petugas',
            message: `Apakah Anda yakin ingin menghapus data ${nama}? Data yang dihapus tidak dapat dipulihkan.`,
            onConfirm: () => {
                all.splice(i, 1);
                saveAll(all);
                renderList();
                tampilToast('Data petugas berhasil dihapus.');
            }
        });
    }



    // Daftar petugas dirender oleh Blade dari database.
--}}
<script>
    function bukaTambah() {
        const form = document.getElementById('petugas-form');
        form.reset();
        form.elements.edit_index.value = '';
        form.elements._method.value = 'POST';
        form.elements.status.value = 'active';
        document.getElementById('modal-judul').textContent = 'Tambah Petugas';
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(officer) {
        const form = document.getElementById('petugas-form');
        form.reset();
        form.elements.edit_index.value = officer.id;
        form.elements._method.value = 'PUT';
        ['name', 'employee_number', 'rank', 'position', 'email', 'phone', 'address', 'joined_at', 'status'].forEach((field) => {
            form.elements[field].value = officer[field] || '';
        });
        form.elements.organizational_unit_id.value = officer.organizational_unit_id || '';
        form.elements.operational_zone_id.value = officer.operational_zone_id || '';
        document.getElementById('modal-judul').textContent = 'Ubah Petugas';
        document.getElementById('modal').classList.remove('hidden');
    }

    function simpanPetugas(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const id = form.elements.edit_index.value;
        form.action = id === ''
            ? @json(route('super_admin.petugas.store'))
            : @json(route('super_admin.petugas.update', ['officer' => '__OFFICER__'])).replace('__OFFICER__', id);
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.classList.add('cursor-wait', 'opacity-70');
        submitButton.textContent = 'Memproses...';
        form.submit();
    }

    function toggleStatus(id) {
        window.konfirmasiHapus({
            title: 'Konfirmasi Perubahan Status',
            message: 'Ubah status petugas ini? Perubahan status dapat memengaruhi penugasan kegiatan.',
            confirmText: 'Ya, Ubah Status',
            onConfirm: () => {
                fetch(@json(route('super_admin.petugas.status', ['officer' => '__OFFICER__'])).replace('__OFFICER__', id), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(async response => {
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Status petugas gagal diubah.');
                    window.tampilkanToastLaluMuatUlang(`Status petugas berhasil diubah menjadi ${result.label}.`);
                }).catch(error => window.tampilToast(error.message, 'error'));
            },
        });
    }

    function hapusPetugas(id, nama) {
        window.konfirmasiHapus({
            title: 'Hapus Petugas',
            message: `Apakah Anda yakin ingin menghapus petugas "${nama}"? Data tetap tersimpan sebagai soft delete.`,
            onConfirm: () => {
                fetch(@json(route('super_admin.petugas.destroy', ['officer' => '__OFFICER__'])).replace('__OFFICER__', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(async response => {
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Petugas gagal dihapus.');
                    window.tampilkanToastLaluMuatUlang(result.message || 'Petugas berhasil dihapus.');
                }).catch(error => window.tampilToast(error.message, 'error'));
            },
        });
    }
</script>
@endpush
