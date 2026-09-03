@extends('layouts.super_admin')

@section('title', 'Evaluasi Petugas')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Evaluasi Kinerja Petugas</h1>
            <p class="text-sm text-slate-500 mt-1">Penilaian performa petugas dalam menangani pengaduan dan pelayanan masyarakat.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-super-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Penilaian
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-super-active/10 text-super-active flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">assessment</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Petugas Dievaluasi</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">star</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Sangat Baik</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-sangat">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">fact_check</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Sudah Dinilai</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-sudah">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">trending_up</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Rata-rata Skor</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-rata">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Penilaian Kinerja</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy + Tambahan Admin</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Peringkat</th>
                        <th class="px-6 py-3 font-semibold">Petugas</th>
                        <th class="px-6 py-3 font-semibold">Regu</th>
                        <th class="px-6 py-3 font-semibold">Laporan Ditangani</th>
                        <th class="px-6 py-3 font-semibold">Selesai</th>
                        <th class="px-6 py-3 font-semibold">Respon Rata-rata</th>
                        <th class="px-6 py-3 font-semibold">Skor</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="evaluasi-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">📊</div>
            <p>Belum ada data evaluasi.</p>
        </div>
    </div>

    {{-- Modal tambah/ubah penilaian --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800" id="modal-judul">Tambah Penilaian</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="evaluasi-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanEvaluasi(event)">
                <input type="hidden" name="edit_index" value="">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Bambang Sutrisno"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Regu / Unit</label>
                        <input type="text" name="regu" placeholder="Contoh: Regu A - Patroli"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Respon Rata-rata (jam)</label>
                        <input type="number" name="respon" min="0" step="0.1" placeholder="Contoh: 2.5"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Laporan Ditangani</label>
                        <input type="number" name="ditangani" min="0" placeholder="Contoh: 25"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Selesai</label>
                        <input type="number" name="selesai" min="0" placeholder="Contoh: 22"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Skor Kinerja (1-100) <span class="text-red-500">*</span></label>
                    <input type="number" name="skor" required min="1" max="100" placeholder="Contoh: 85"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupModal()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-super-active text-white px-6 py-2 rounded-xl font-medium hover:bg-brand-600 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-lg text-sm z-50"></div>
@endsection

@push('scripts')
<script>
    const KODE = 'sipraja_evaluasi';

    function kat(skor) {
        if (skor >= 85) return { label: 'Sangat Baik', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
        if (skor >= 70) return { label: 'Baik', cls: 'bg-blue-50 text-blue-700 border-blue-200' };
        if (skor >= 55) return { label: 'Cukup', cls: 'bg-amber-50 text-amber-700 border-amber-200' };
        return { label: 'Perlu Perbaikan', cls: 'bg-red-50 text-red-700 border-red-200' };
    }

    function getData() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { nama: 'Joko Widodo S', regu: 'Regu A - Patroli', ditangani: 28, selesai: 26, respon: 1.5, skor: 92 },
                { nama: 'Sri Wahyuni', regu: 'Regu B - Patroli', ditangani: 24, selesai: 22, respon: 2.0, skor: 88 },
                { nama: 'Bambang Sutrisno', regu: 'Regu A - Patroli', ditangani: 22, selesai: 19, respon: 3.2, skor: 80 },
                { nama: 'Agus Riyanto', regu: 'Regu B - Patroli', ditangani: 15, selesai: 11, respon: 5.0, skor: 66 },
                { nama: 'Darmawan Hadi', regu: 'Regu A - Patroli', ditangani: 10, selesai: 5, respon: 7.5, skor: 50 },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function saveAll(data) { localStorage.setItem(KODE, JSON.stringify(data)); }

    function renderList() {
        const data = getData().slice().sort((a, b) => b.skor - a.skor);

        document.getElementById('stat-total').textContent = data.length;
        document.getElementById('stat-sangat').textContent = data.filter(e => e.skor >= 85).length;
        document.getElementById('stat-sudah').textContent = data.length;
        document.getElementById('stat-rata').textContent = data.length ? Math.round(data.reduce((s, e) => s + e.skor, 0) / data.length) : 0;

        const tbody = document.getElementById('evaluasi-list');
        const kosong = document.getElementById('kosong');

        if (data.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = data.map((e, i) => {
            const k = kat(e.skor);
            const medali = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : (i + 1);
            const gi = getData().findIndex(x => x.nama === e.nama && x.skor === e.skor && x.regu === e.regu);
            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${i < 3 ? 'bg-amber-50' : 'bg-slate-100'} font-bold text-sm">${medali}</span>
                    </td>
                    <td class="px-6 py-3.5 font-medium text-slate-800">${e.nama}</td>
                    <td class="px-6 py-3.5 text-slate-500">${e.regu || '-'}</td>
                    <td class="px-6 py-3.5 text-slate-500">${e.ditangani || 0}</td>
                    <td class="px-6 py-3.5 text-slate-500">${e.selesai || 0}</td>
                    <td class="px-6 py-3.5 text-slate-500">${e.respon != null ? e.respon + ' jam' : '-'}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full ${e.skor >= 85 ? 'bg-emerald-500' : e.skor >= 70 ? 'bg-blue-500' : e.skor >= 55 ? 'bg-amber-500' : 'bg-red-500'}" style="width:${e.skor}%"></div>
                            </div>
                            <span class="font-bold text-slate-800">${e.skor}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full border ${k.cls}">${k.label}</span>
                    </td>
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <button type="button" onclick="bukaEdit(${gi})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Edit">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button type="button" onclick="hapus(${gi})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }


    function bukaTambah() {
        document.getElementById('modal-judul').textContent = 'Tambah Penilaian';
        document.getElementById('evaluasi-form').reset();
        document.querySelector('[name=edit_index]').value = '';
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(i) {
        const e = getData()[i];
        const f = document.getElementById('evaluasi-form');
        f.reset();
        document.getElementById('modal-judul').textContent = 'Ubah Penilaian';
        document.querySelector('[name=edit_index]').value = i;
        f.nama.value = e.nama;
        f.regu.value = e.regu || '';
        f.respon.value = e.respon != null ? e.respon : '';
        f.ditangani.value = e.ditangani || '';
        f.selesai.value = e.selesai || '';
        f.skor.value = e.skor;
        document.getElementById('modal').classList.remove('hidden');
    }

    function tutupModal() { document.getElementById('modal').classList.add('hidden'); }

    function simpanEvaluasi(e) {
        e.preventDefault();
        const f = document.getElementById('evaluasi-form');
        const data = {
            nama: f.nama.value.trim(),
            regu: f.regu.value.trim(),
            respon: f.respon.value !== '' ? Number(f.respon.value) : null,
            ditangani: f.ditangani.value !== '' ? Number(f.ditangani.value) : 0,
            selesai: f.selesai.value !== '' ? Number(f.selesai.value) : 0,
            skor: Number(f.skor.value),
        };

        const all = getData();
        const idx = document.querySelector('[name=edit_index]').value;
        if (idx !== '') {
            all[idx] = data;
            tampilToast('Penilaian berhasil diperbarui.');
        } else {
            all.unshift(data);
            tampilToast('Penilaian berhasil ditambahkan.');
        }
        saveAll(all);
        renderList();
        tutupModal();
    }

    function hapus(i) {
        const all = getData();
        if (!confirm('Yakin ingin menghapus penilaian ' + all[i].nama + '?')) return;
        all.splice(i, 1);
        saveAll(all);
        renderList();
        tampilToast('Penilaian berhasil dihapus.');
    }

    function tampilToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2500);
    }

    renderList();
</script>
@endpush
