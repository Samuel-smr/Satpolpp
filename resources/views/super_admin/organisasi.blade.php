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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-divisi">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-anggota">0</p>
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
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-zona">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Unit Kerja</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy + Tambahan Admin</span>
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
                <tbody id="unit-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">🏢</div>
            <p>Belum ada unit kerja.</p>
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
                <input type="hidden" name="edit_index" value="">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Unit <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Seksi Ketertiban Umum"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Jenis <span class="text-red-500">*</span></label>
                        <select name="jenis" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                            <option value="">Pilih Jenis</option>
                            <option value="Divisi">Divisi / Seksi</option>
                            <option value="Regu">Regu / Tim</option>
                            <option value="Pos">Pos Jaga</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pimpinan / Kepala</label>
                        <select name="pimpinan" id="pimpinan-select"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none bg-white">
                            <option value="">Pilih User</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Zona / Wilayah Operasi</label>
                    <input type="text" name="zona" placeholder="Contoh: Kecamatan Wates, Kulon Progo"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Jumlah Anggota</label>
                    <input type="number" name="anggota" min="1" placeholder="Contoh: 10"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
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
    const KODE = 'sipraja_organisasi';
    let editIndex = null;

    function getUnits() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { nama: 'Kantor Satpol PP Kulon Progo', jenis: 'Divisi', pimpinan: 'Kasat Pol PP', zona: 'Kabupaten Kulon Progo', anggota: 45 },
                { nama: 'Seksi Ketertiban Umum', jenis: 'Divisi', pimpinan: 'Kasi. Ketertiban', zona: 'Kulon Progo', anggota: 15 },
                { nama: 'Seksi Penegakan Perda', jenis: 'Divisi', pimpinan: 'Kasi. Penegakan Perda', zona: 'Kulon Progo', anggota: 12 },
                { nama: 'Regu A - Patroli', jenis: 'Regu', pimpinan: 'Kanit. Regu A', zona: 'Kecamatan Wates', anggota: 8 },
                { nama: 'Regu B - Patroli', jenis: 'Regu', pimpinan: 'Kanit. Regu B', zona: 'Kecamatan Pengasih', anggota: 8 },
                { nama: 'Pos Jaga Alun-alun', jenis: 'Pos', pimpinan: 'Komandan Pos', zona: 'Alun-alun Wates', anggota: 5 },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function saveAll(data) { localStorage.setItem(KODE, JSON.stringify(data)); }

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
        isiPimpinanOptions('');
        document.getElementById('modal').classList.remove('hidden');
    }

    function isiPimpinanOptions(selected) {
        const select = document.getElementById('pimpinan-select');
        let users = [];
        try {
            users = JSON.parse(localStorage.getItem('sipraja_users') || '[]');
        } catch (e) { users = []; }
        select.innerHTML = '';
        const kosong = document.createElement('option');
        kosong.value = '';
        kosong.textContent = users.length ? 'Pilih User' : 'Belum ada user di Manajemen User';
        select.appendChild(kosong);
        users.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.nama;
            opt.textContent = u.nama + (u.username ? ' (' + u.username + ')' : '');
            select.appendChild(opt);
        });
        select.value = selected;
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
        isiPimpinanOptions(u.pimpinan || '');
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



    renderList();
</script>
@endpush
