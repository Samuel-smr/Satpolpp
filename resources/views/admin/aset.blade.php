@extends('layouts.admin')

@section('title', 'Aset Operasional')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Inventaris Aset Operasional</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar sarana, prasarana, kendaraan dinas, dan perlengkapan Satpol PP.</p>
        </div>

        <button type="button" onclick="bukaForm()"
            class="inline-flex items-center justify-center gap-2 bg-brand-600 text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-700 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Aset
        </button>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">inventory_2</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Aset</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Kondisi Baik</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-baik">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">build</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Perlu Perbaikan</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-perbaikan">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">directions_car</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Kendaraan Dinas</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-kendaraan">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Aset --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Aset</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Dummy + Tambahan Admin</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Nama Aset</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        <th class="px-6 py-3 font-semibold">Kondisi</th>
                        <th class="px-6 py-3 font-semibold">Lokasi</th>
                        <th class="px-6 py-3 font-semibold">Tahun</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="aset-list"></tbody>
            </table>
        </div>

        <div id="aset-kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">📦</div>
            <p>Belum ada data aset.</p>
        </div>
    </div>

    {{-- Modal Tambah Aset --}}
    <div id="modal-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupForm()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800">Tambah Aset</h2>
                <button type="button" onclick="tutupForm()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="aset-form" class="p-6 space-y-4" onsubmit="simpanAset(event)">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Aset <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Mobil Patroli"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                            <option value="">Pilih Kategori</option>
                            <option>Kendaraan</option>
                            <option>Perlengkapan</option>
                            <option>Peralatan</option>
                            <option>Sarana &amp; Prasarana</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kondisi <span class="text-red-500">*</span></label>
                        <select name="kondisi" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                            <option value="">Pilih Kondisi</option>
                            <option>Baik</option>
                            <option>Perlu Perbaikan</option>
                            <option>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Lokasi</label>
                        <input type="text" name="lokasi" placeholder="Contoh: Kantor Satpol PP"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tahun Perolehan</label>
                        <input type="number" name="tahun" placeholder="Contoh: 2022"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupForm()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-brand-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-brand-700 transition-colors">Simpan Aset</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-lg text-sm z-50"></div>
@endsection

@push('scripts')
<script>
    const KODE = 'sipraja_aset';

    function getAset() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { nama: 'Mobil Patroli Dalmas', kategori: 'Kendaraan', kondisi: 'Baik', lokasi: 'Kantor Satpol PP', tahun: '2020' },
                { nama: 'Mobil Pick Up Operasional', kategori: 'Kendaraan', kondisi: 'Perlu Perbaikan', lokasi: 'Kantor Satpol PP', tahun: '2018' },
                { nama: 'Sepeda Motor Dinas', kategori: 'Kendaraan', kondisi: 'Baik', lokasi: 'Pos Jaga Alun-alun', tahun: '2021' },
                { nama: 'HT / Radio Komunikasi', kategori: 'Peralatan', kondisi: 'Baik', lokasi: 'Kantor Satpol PP', tahun: '2022' },
                { nama: 'Tenda Penertiban', kategori: 'Perlengkapan', kondisi: 'Baik', lokasi: 'Gudang', tahun: '2019' },
                { nama: 'Sound System Portable', kategori: 'Peralatan', kondisi: 'Perlu Perbaikan', lokasi: 'Gudang', tahun: '2019' },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function kondisiClass(k) {
        if (k === 'Baik') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        if (k === 'Perlu Perbaikan') return 'bg-amber-50 text-amber-700 border-amber-200';
        return 'bg-red-50 text-red-700 border-red-200';
    }

    function renderAset() {
        const data = getAset();
        const tbody = document.getElementById('aset-list');
        const kosong = document.getElementById('aset-kosong');

        document.getElementById('stat-total').textContent = data.length;
        document.getElementById('stat-baik').textContent = data.filter(a => a.kondisi === 'Baik').length;
        document.getElementById('stat-perbaikan').textContent = data.filter(a => a.kondisi === 'Perlu Perbaikan' || a.kondisi === 'Rusak Berat').length;
        document.getElementById('stat-kendaraan').textContent = data.filter(a => a.kategori === 'Kendaraan').length;

        if (data.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = data.map((a, i) => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-6 py-3.5 font-medium text-slate-800">${a.nama}</td>
                <td class="px-6 py-3.5 text-slate-500">${a.kategori}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border ${kondisiClass(a.kondisi)}">${a.kondisi}</span>
                </td>
                <td class="px-6 py-3.5 text-slate-500">${a.lokasi || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${a.tahun || '-'}</td>
                <td class="px-6 py-3.5 text-right">
                    <button type="button" onclick="hapusAset(${i})" class="text-red-500 hover:text-red-700" title="Hapus">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function bukaForm() {
        document.getElementById('modal-form').classList.remove('hidden');
        document.getElementById('aset-form').reset();
    }

    function tutupForm() {
        document.getElementById('modal-form').classList.add('hidden');
    }

    function simpanAset(e) {
        e.preventDefault();
        const f = document.getElementById('aset-form');
        const data = getAset();
        data.unshift({
            nama: f.nama.value.trim(),
            kategori: f.kategori.value,
            kondisi: f.kondisi.value,
            lokasi: f.lokasi.value.trim(),
            tahun: f.tahun.value.trim(),
        });
        localStorage.setItem(KODE, JSON.stringify(data));
        renderAset();
        tutupForm();
        tampilToast('Aset berhasil ditambahkan.');
    }

    function hapusAset(i) {
        const data = getAset();
        data.splice(i, 1);
        localStorage.setItem(KODE, JSON.stringify(data));
        renderAset();
        tampilToast('Aset berhasil dihapus.');
    }

    function tampilToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2500);
    }

    renderAset();
</script>
@endpush
