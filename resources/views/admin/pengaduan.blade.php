@extends('layouts.admin')

@section('title', 'Pengaduan & Informasi')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Pengaduan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau semua laporan serta informasi dari masyarakat.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-brand-600 text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-700 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Laporan
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">inbox</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Masuk</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">emergency</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Laporan Darurat</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-darurat">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">info</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Informasi & Pengaduan</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-informasi">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">task_alt</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Selesai</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-selesai">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
            <div class="flex gap-2 flex-wrap">
                <button type="button" data-tab="semua" onclick="setTab('semua')"
                    class="tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors">Semua</button>
                <button type="button" data-tab="darurat" onclick="setTab('darurat')"
                    class="tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors">Laporan Darurat</button>
                <button type="button" data-tab="informasi" onclick="setTab('informasi')"
                    class="tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors">Informasi & Pengaduan</button>
            </div>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data Tersinkron dari Pelapor</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">No. Laporan</th>
                        <th class="px-6 py-3 font-semibold">Jenis</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        <th class="px-6 py-3 font-semibold">Lokasi</th>
                        <th class="px-6 py-3 font-semibold">Telepon</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="report-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">📭</div>
            <p>Belum ada laporan.</p>
        </div>
    </div>

    {{-- Detail & tindak lanjut --}}
    <div id="detail" class="hidden mt-8">
        <button type="button" onclick="tutupDetail()" class="mb-4 inline-flex items-center gap-2 text-brand-600 font-medium hover:underline">
            ← <span>Kembali ke daftar</span>
        </button>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <h2 class="font-headline font-bold text-lg text-slate-800" id="d-kode">--</h2>
                        <span class="text-sm font-semibold px-3 py-1 rounded-full" id="d-badge">--</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 text-sm bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
                        <div>
                            <span class="text-slate-400">Jenis:</span>
                            <span class="font-medium" id="d-jenis">--</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Kategori:</span>
                            <span class="font-medium" id="d-kategori">--</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Nomor Telepon:</span>
                            <span class="font-medium" id="d-telepon">--</span>
                        </div>
                        <div>
                            <span class="text-slate-400">NIK:</span>
                            <span class="font-medium" id="d-nik">--</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Lokasi:</span>
                            <span class="font-medium" id="d-lokasi">--</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Waktu lapor:</span>
                            <span class="font-medium" id="d-waktu">--</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400">Deskripsi:</span>
                            <span class="font-medium" id="d-deskripsi">--</span>
                        </div>
                    </div>

                    <h3 class="font-headline font-bold text-slate-800 mb-5">Pelacakan Laporan</h3>
                    <ol class="relative ml-1" id="tracking-list"></ol>
                </div>
            </div>

            <div>
                <div class="bg-slate-800 text-white border rounded-2xl p-6 shadow-sm sticky top-24">
                    <h3 class="font-headline font-bold mb-4">Tindak Lanjut</h3>
                    <p class="text-sm text-slate-300 mb-4">Perbarui status penanganan laporan ini.</p>

                    <div class="space-y-3">
                        <button type="button" onclick="majuStatus(1)" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-xl font-medium transition-colors">Tandai Diterima</button>
                        <button type="button" onclick="majuStatus(2)" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-medium transition-colors">Mulai Verifikasi</button>
                        <button type="button" onclick="majuStatus(4)" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl font-medium transition-colors">Tandai Ditindaklanjuti</button>
                        <button type="button" onclick="majuStatus(5)" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-medium transition-colors">Selesaikan Laporan</button>
                    </div>

                    <div class="mt-5 pt-5 border-t border-white/10">
                        <button type="button" onclick="hapusLaporan()" class="w-full border border-red-400/50 text-red-300 hover:bg-red-500/10 py-2.5 rounded-xl font-medium transition-colors">Hapus Laporan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal tambah laporan --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupTambah()"></div>
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800">Tambah Laporan</h2>
                <button type="button" onclick="tutupTambah()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="tambah-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanLaporan(event)">
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis <span class="text-red-500">*</span></label>
                    <select name="jenis" required class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                        <option value="">Pilih Jenis</option>
                        <option value="darurat">Lapor Darurat</option>
                        <option value="informasi">Informasi & Pengaduan</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                        <input type="tel" name="telepon" required placeholder="081234567890"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NIK (Opsional)</label>
                        <input type="text" name="nik" placeholder="16 digit NIK"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                        <option value="">Pilih Kategori</option>
                        <option>Ketertiban Umum</option>
                        <option>Keamanan</option>
                        <option>Patroli</option>
                        <option>Pengawalan</option>
                        <option>Kebersihan Lingkungan</option>
                        <option>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="deskripsi" rows="3" required placeholder="Jelaskan kejadian..."
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Alamat / titik lokasi"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupTambah()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-brand-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-brand-700 transition-colors">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-lg text-sm z-50"></div>
@endsection

@push('scripts')
<script>
    const STATUSES = [
        ['Laporan dibuat', 'Laporan telah kami terima.'],
        ['Laporan diterima', 'Tim kami telah menerima laporan.'],
        ['Sedang diverifikasi', 'Sedang memeriksa kebenaran laporan.'],
        ['Ditindaklanjuti', 'Laporan sedang ditangani petugas.'],
        ['Penanganan selesai', 'Penanganan di lokasi selesai.'],
        ['Laporan selesai', 'Laporan telah selesai ditangani.'],
    ];
    const KODE = 'sipraja_reports';
    let tab = 'semua';
    let currentIndex = null;

    function getReports() {
        let data = JSON.parse(localStorage.getItem(KODE) || '[]');
        if (data.length === 0) {
            data = [
                { kode: 'SPJ-584021', jenis: 'darurat', kategori: 'Ketertiban Umum', deskripsi: 'Kerumunan massa di alun-alun yang berpotensi gangguan.', lokasi: 'Alun-alun Kulon Progo', telepon: '081234567890', nik: '', dibuat: '31/08/2026 09.12', statusIdx: 3 },
                { kode: 'SPJ-583990', jenis: 'informasi', kategori: 'Kebersihan Lingkungan', deskripsi: 'Tumpukan sampah di pinggir jalan utama.', lokasi: 'Jl. Sugiman, Pengasih', telepon: '082134567890', nik: '', dibuat: '30/08/2026 16.45', statusIdx: 5 },
                { kode: 'SPJ-583772', jenis: 'informasi', kategori: 'Keamanan', deskripsi: 'Orang mabuk berkeliaran di area pasar.', lokasi: 'Pasar Wates', telepon: '083145678901', nik: '', dibuat: '29/08/2026 20.03', statusIdx: 1 },
            ];
            localStorage.setItem(KODE, JSON.stringify(data));
        }
        return data;
    }

    function saveAll(data) { localStorage.setItem(KODE, JSON.stringify(data)); }

    function labelJenis(j) { return j === 'darurat' ? 'Lapor Darurat' : 'Informasi & Pengaduan'; }
    function labelStatus(idx) { return STATUSES[idx] ? STATUSES[idx][0] : 'Dikirim'; }
    function jenisClass(j) { return j === 'darurat' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'; }
    function badgeClass(idx) {
        if (idx >= 5) return 'bg-emerald-100 text-emerald-700';
        if (idx >= 3) return 'bg-amber-100 text-amber-700';
        if (idx >= 1) return 'bg-blue-100 text-blue-700';
        return 'bg-slate-100 text-slate-600';
    }

    function filtered(data) {
        if (tab === 'darurat') return data.filter(r => r.jenis === 'darurat');
        if (tab === 'informasi') return data.filter(r => r.jenis !== 'darurat');
        return data;
    }

    function setTab(t) {
        tab = t;
        document.querySelectorAll('.tab-btn').forEach(b => {
            const active = b.dataset.tab === t;
            b.className = 'tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors ' +
                (active ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50');
        });
        renderList();
    }

    function renderList() {
        const all = getReports();
        document.getElementById('stat-total').textContent = all.length;
        document.getElementById('stat-darurat').textContent = all.filter(r => r.jenis === 'darurat').length;
        document.getElementById('stat-informasi').textContent = all.filter(r => r.jenis !== 'darurat').length;
        document.getElementById('stat-selesai').textContent = all.filter(r => (r.statusIdx != null ? r.statusIdx : 0) >= 5).length;

        const list = filtered(all);
        const tbody = document.getElementById('report-list');
        const kosong = document.getElementById('kosong');

        if (list.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = list.map((r, i) => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50 cursor-pointer" onclick="bukaDetail(${i})">
                <td class="px-6 py-3.5 font-medium text-slate-800">${r.kode}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${jenisClass(r.jenis)}">${labelJenis(r.jenis)}</span>
                </td>
                <td class="px-6 py-3.5 text-slate-500">${r.kategori || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${r.lokasi || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${r.telepon || '-'}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${badgeClass(r.statusIdx)}">${labelStatus(r.statusIdx)}</span>
                </td>
                <td class="px-6 py-3.5 text-right">
                    <button type="button" onclick="event.stopPropagation(); bukaDetail(${i})" class="text-brand-600 hover:text-brand-700" title="Detail">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                    <button type="button" onclick="event.stopPropagation(); hapus(${i})" class="text-red-500 hover:text-red-700 ml-2" title="Hapus">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function findGlobalIndex(detailIdx) {
        const list = filtered(getReports());
        const item = list[detailIdx];
        return getReports().findIndex(r => r.kode === item.kode);
    }

    function bukaDetail(detailIdx) {
        const all = getReports();
        const gi = findGlobalIndex(detailIdx);
        currentIndex = gi;
        const r = all[gi];
        const idx = r.statusIdx != null ? r.statusIdx : 0;

        document.getElementById('d-kode').textContent = r.kode;
        document.getElementById('d-badge').textContent = labelStatus(idx);
        document.getElementById('d-badge').className = 'text-sm font-semibold px-3 py-1 rounded-full ' + badgeClass(idx);
        document.getElementById('d-jenis').textContent = labelJenis(r.jenis);
        document.getElementById('d-kategori').textContent = r.kategori || '-';
        document.getElementById('d-telepon').textContent = r.telepon || '-';
        document.getElementById('d-nik').textContent = r.nik || '-';
        document.getElementById('d-lokasi').textContent = r.lokasi || '-';
        document.getElementById('d-waktu').textContent = r.dibuat || r.waktu || '-';
        document.getElementById('d-deskripsi').textContent = r.deskripsi || '-';

        const total = STATUSES.length;
        document.getElementById('tracking-list').innerHTML = STATUSES.map(([title, desc], i) => {
            const done = i <= idx;
            const isLast = i === total - 1;
            return `
                <li class="relative pl-8 ${isLast ? '' : 'pb-6'}">
                    ${isLast ? '' : '<span class="absolute left-0 top-6 bottom-0 w-0.5 bg-slate-200"></span>'}
                    <span class="absolute left-0 top-1 w-4 h-4 -translate-x-1/2 rounded-full border-2 border-white ring-2 ${done ? 'bg-emerald-500 ring-emerald-500' : 'bg-slate-300 ring-slate-300'}"></span>
                    <h4 class="font-semibold ${done ? 'text-slate-800' : 'text-slate-400'}">${title}</h4>
                    <p class="text-sm ${done ? 'text-slate-500' : 'text-slate-400'}">${desc}</p>
                </li>
            `;
        }).join('');

        document.getElementById('detail').classList.remove('hidden');
        document.getElementById('detail').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function tutupDetail() {
        document.getElementById('detail').classList.add('hidden');
        renderList();
    }

    function majuStatus(newIdx) {
        if (currentIndex == null) return;
        const all = getReports();
        const cur = all[currentIndex].statusIdx != null ? all[currentIndex].statusIdx : 0;
        if (newIdx <= cur) { alert('Status tidak bisa mundur.'); return; }
        all[currentIndex].statusIdx = newIdx;
        saveAll(all);
        bukaDetail(currentIndex);
        tampilToast('Status diperbarui menjadi: ' + labelStatus(newIdx));
    }

    function hapus(detailIdx) {
        const all = getReports();
        const gi = findGlobalIndex(detailIdx);
        if (!confirm('Yakin ingin menghapus laporan ' + all[gi].kode + '?')) return;
        all.splice(gi, 1);
        saveAll(all);
        renderList();
        tampilToast('Laporan berhasil dihapus.');
    }

    function hapusLaporan() {
        if (currentIndex == null) return;
        const all = getReports();
        if (!confirm('Yakin ingin menghapus laporan ' + all[currentIndex].kode + '?')) return;
        all.splice(currentIndex, 1);
        saveAll(all);
        tutupDetail();
        tampilToast('Laporan berhasil dihapus.');
    }

    function bukaTambah() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('tambah-form').reset();
    }
    function tutupTambah() { document.getElementById('modal').classList.add('hidden'); }

    function simpanLaporan(e) {
        e.preventDefault();
        const f = document.getElementById('tambah-form');
        const all = getReports();
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const dibuat = `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}.${pad(now.getMinutes())}`;
        const kode = 'SPJ-' + String(100000 + Math.floor(Math.random() * 900000));

        all.unshift({
            kode: kode,
            jenis: f.jenis.value,
            telepon: f.telepon.value.trim(),
            nik: f.nik.value.trim(),
            kategori: f.kategori.value,
            deskripsi: f.deskripsi.value.trim(),
            lokasi: f.lokasi.value.trim(),
            dibuat: dibuat,
            statusIdx: 0,
        });
        saveAll(all);
        renderList();
        tutupTambah();
        tampilToast('Laporan ' + kode + ' berhasil ditambahkan.');
    }

    function tampilToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2500);
    }

    setTab('semua');
</script>
@endpush
