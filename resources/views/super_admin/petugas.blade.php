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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-aktif">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-cuti">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-nonaktif">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Personil</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy + Tambahan Admin</span>
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
                <tbody id="petugas-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">🪪</div>
            <p>Belum ada data petugas.</p>
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
                <input type="hidden" name="edit_index" value="">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Joko Widodo Samudra"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">NIP / KTA <span class="text-red-500">*</span></label>
                        <input type="text" name="nip" required placeholder="Contoh: 19750101 202001 1 001"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pangkat / Jabatan</label>
                        <input type="text" name="pangkat" placeholder="Contoh: Penata Muda / Anggota"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Regu / Unit</label>
                        <input type="text" name="regu" placeholder="Contoh: Regu A - Patroli"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Zona Operasi</label>
                        <input type="text" name="zona" placeholder="Contoh: Kecamatan Wates"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                        <option value="">Pilih Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="cuti">Cuti</option>
                        <option value="nonaktif">Nonaktif</option>
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
<script>
    const KODE = 'sipraja_petugas';

    function getPetugas() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { nama: 'Joko Widodo S', nip: '19750101 202001 1 001', pangkat: 'Penata Muda / Anggota', regu: 'Regu A - Patroli', zona: 'Kecamatan Wates', status: 'aktif' },
                { nama: 'Bambang Sutrisno', nip: '19780212 201402 1 002', pangkat: 'Pengatur / Anggota', regu: 'Regu A - Patroli', zona: 'Kecamatan Wates', status: 'aktif' },
                { nama: 'Sri Wahyuni', nip: '19850315 201903 2 003', pangkat: 'Penata Muda / Anggota', regu: 'Regu B - Patroli', zona: 'Kecamatan Pengasih', status: 'aktif' },
                { nama: 'Agus Riyanto', nip: '19900123 201601 1 004', pangkat: 'Pengatur Muda / Anggota', regu: 'Regu B - Patroli', zona: 'Kecamatan Pengasih', status: 'cuti' },
                { nama: 'Darmawan Hadi', nip: '19731205 201101 1 005', pangkat: 'Penata / Kepala Regu', regu: 'Regu A - Patroli', zona: 'Kecamatan Wates', status: 'nonaktif' },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function saveAll(data) { localStorage.setItem(KODE, JSON.stringify(data)); }

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



    renderList();
</script>
@endpush
