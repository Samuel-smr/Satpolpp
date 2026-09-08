@extends('layouts.admin')

@section('title', 'Pengaduan & Informasi')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Pengaduan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau semua laporan serta informasi dari masyarakat.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-sidebar-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-sidebar-hover transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Laporan
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Total Masuk</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1" id="stat-total">0</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-orange-100/70 text-orange-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">inbox</span>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Laporan Darurat</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1" id="stat-darurat">0</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100/70 text-red-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">emergency</span>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Informasi & Pengaduan</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1" id="stat-informasi">0</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-brand-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">info</span>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-600 leading-snug">Selesai</p>
                    <p class="font-headline text-3xl font-bold text-slate-900 mt-1" id="stat-selesai">0</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px]">task_alt</span>
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
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                        <th class="px-6 py-3.5 font-semibold text-left">No. Laporan</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Jenis</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Sumber</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Kategori</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Lokasi</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Telepon</th>
                        <th class="px-6 py-3.5 font-semibold text-left">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
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
                    <select name="jenis" required class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none bg-white">
                        <option value="">Pilih Jenis</option>
                        <option value="darurat">Lapor Darurat</option>
                        <option value="informasi">Informasi & Pengaduan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Sumber Laporan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="setSumber('online')" id="sumber-btn-online"
                            class="flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[20px]">public</span> Online
                        </button>
                        <button type="button" onclick="setSumber('offline')" id="sumber-btn-offline"
                            class="flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-[20px]">person_pin</span> Offline
                        </button>
                    </div>
                </div>

                {{-- Platform (Online) --}}
                <div id="sumber-online" class="hidden">
                    <label class="block text-sm font-medium mb-1">Platform Media Sosial <span class="text-red-500">*</span></label>
                    <select name="platform" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none bg-white">
                        <option value="">Pilih Platform</option>
                        <option>WhatsApp</option>
                        <option>Instagram</option>
                        <option>X (Twitter)</option>
                        <option>Facebook</option>
                        <option>TikTok</option>
                        <option>Lainnya</option>
                    </select>
                </div>

                {{-- Nama + NIK (Offline) --}}
                <div id="sumber-offline" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Pelapor <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_offline" placeholder="Nama lengkap pelapor"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NIK <span class="text-red-500">*</span></label>
                        <input type="text" name="nik_offline" maxlength="16" placeholder="16 digit NIK"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="telepon" placeholder="08123456789"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="deskripsi" rows="3" required placeholder="Jelaskan kejadian..."
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Lokasi Kejadian <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Alamat / detail lokasi"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none">
                </div>

                {{-- Peta --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Titik Lokasi di Peta</label>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Latitude</label>
                            <input type="text" name="lat" readonly placeholder="Klik peta"
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Longitude</label>
                            <input type="text" name="lng" readonly placeholder="Klik peta"
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                        </div>
                    </div>
                    <div class="flex gap-2 mb-3">
                        <div class="flex-1 relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                            <input type="text" id="cari-lokasi-input" placeholder="Cari lokasi / alamat..." onkeydown="if(event.key==='Enter'){event.preventDefault();geocodeCariLokasi();}"
                                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>
                        <button type="button" onclick="geocodeCariLokasi()" class="inline-flex items-center justify-center gap-1.5 bg-sidebar-active hover:bg-sidebar-hover text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-colors">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                            Cari
                        </button>
                    </div>
                    <div class="relative">
                        <div id="map-picker" class="w-full h-64 bg-slate-100 rounded-xl border border-slate-200 overflow-hidden z-10"></div>
                        <span class="absolute top-3 right-3 z-[1000] bg-white/90 backdrop-blur text-xs font-medium px-3 py-1.5 rounded-lg shadow-sm border border-slate-200 text-slate-600">
                            Klik peta untuk tentukan titik
                        </span>
                    </div>
                </div>

                {{-- Upload Foto & Video --}}
                <div>
                    <label class="block text-sm font-medium mb-2">Foto &amp; Video Bukti (Opsional)</label>
                    <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-xl p-6 text-center cursor-pointer hover:border-brand-500 hover:bg-slate-100 transition-colors">
                        <input type="file" id="file-input" multiple accept="image/*,video/*" class="absolute opacity-0 w-0 h-0" onchange="handleFiles(this.files)">
                        <span class="material-symbols-outlined text-3xl text-slate-400">cloud_upload</span>
                        <p class="text-sm font-medium text-slate-600 mt-1">Klik untuk pilih Foto / Video</p>
                        <p class="text-xs text-slate-400 mt-0.5">Bisa pilih lebih dari 1 file (JPG, PNG, MP4)</p>
                    </label>
                    <div id="file-preview" class="mt-3 grid grid-cols-3 gap-3"></div>
                </div>

                <input type="hidden" name="sumber" value="online">

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupTambah()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-sidebar-active text-white px-6 py-2 rounded-xl font-medium hover:bg-sidebar-hover transition-colors">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>



@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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
                (active ? 'bg-sidebar-active text-white border-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50');
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
            <tr class="border-b border-slate-50 hover:bg-slate-50/50 cursor-pointer" onclick="bukaDetail('${r.kode}')">
                <td class="px-6 py-3.5 font-medium text-slate-800">${r.kode}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${jenisClass(r.jenis)}">${labelJenis(r.jenis)}</span>
                </td>
                <td class="px-6 py-3.5">
                    ${r.sumber_asal === 'offline'
                        ? '<span class="text-xs font-semibold px-2.5 py-1 rounded-full border bg-slate-100 text-slate-600 border-slate-200">Offline</span>'
                        : '<span class="text-xs font-medium text-slate-500"><span class="material-symbols-outlined text-[14px] align-middle">public</span> ' + (r.platform || 'Online') + '</span>'}
                </td>
                <td class="px-6 py-3.5 text-slate-500">${r.kategori || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${r.lokasi || '-'}</td>
                <td class="px-6 py-3.5 text-slate-500">${r.telepon || '-'}</td>
                <td class="px-6 py-3.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${badgeClass(r.statusIdx)}">${labelStatus(r.statusIdx)}</span>
                </td>
                <td class="px-6 py-3.5 text-right">
                    <button type="button" onclick="event.stopPropagation(); bukaDetail('${r.kode}')" class="text-sidebar-active hover:text-sidebar-hover" title="Detail">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                    <button type="button" onclick="event.stopPropagation(); hapus(${i})" class="text-red-500 hover:text-red-700 ml-2" title="Hapus">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function bukaDetail(kode) {
        window.location.href = "{{ url('admin/pengaduan') }}/" + encodeURIComponent(kode);
    }

    function findGlobalIndex(detailIdx) {
        const list = filtered(getReports());
        const item = list[detailIdx];
        return getReports().findIndex(r => r.kode === item.kode);
    }

    function hapus(detailIdx) {
        const all = getReports();
        const gi = findGlobalIndex(detailIdx);
        if (gi === -1 || !all[gi]) return;
        
        window.konfirmasiHapus({
            title: 'Hapus Laporan',
            message: 'Apakah Anda yakin ingin menghapus laporan ' + all[gi].kode + '? Data yang dihapus tidak dapat dipulihkan.',
            onConfirm: () => {
                all.splice(gi, 1);
                saveAll(all);
                renderList();
                tampilToast('Laporan berhasil dihapus.');
            }
        });
    }

    function bukaTambah() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('tambah-form').reset();
        currentFiles = [];
        renderPreviews();
        setSumber('online');
        initMap();
    }
    function tutupTambah() {
        document.getElementById('modal').classList.add('hidden');
        if (mapInstance) mapInstance.remove();
        mapInstance = null;
        markerInstance = null;
    }

    // ===== Sumber Online / Offline =====
    let sumber = 'online';
    function setSumber(s) {
        sumber = s;
        const on = document.getElementById('sumber-btn-online');
        const off = document.getElementById('sumber-btn-offline');
        const base = 'flex items-center justify-center gap-2 p-3 border-2 rounded-xl text-sm font-medium transition-colors ';
        on.className = base + (s === 'online' ? 'border-sidebar-active bg-orange-50 text-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50');
        off.className = base + (s === 'offline' ? 'border-sidebar-active bg-orange-50 text-sidebar-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50');
        document.querySelector('[name=sumber]').value = s;

        const mrOnline = document.getElementById('sumber-online');
        const mrOffline = document.getElementById('sumber-offline');
        mrOnline.classList.toggle('hidden', s !== 'online');
        mrOffline.classList.toggle('hidden', s !== 'offline');

        const platform = document.querySelector('[name=platform]');
        const nama = document.querySelector('[name=nama_offline]');
        const nik = document.querySelector('[name=nik_offline]');
        platform.required = s === 'online';
        nama.required = s === 'offline';
        nik.required = s === 'offline';
    }

    // ===== Peta =====
    let mapInstance = null;
    let markerInstance = null;
    function initMap() {
        if (mapInstance) { mapInstance.remove(); mapInstance = null; markerInstance = null; }
        mapInstance = KulonProgoMap.setup(L.map('map-picker'));
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapInstance);
        mapInstance.on('click', function (e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            document.querySelector('[name=lat]').value = lat;
            document.querySelector('[name=lng]').value = lng;
            if (markerInstance) markerInstance.setLatLng(e.latlng);
            else markerInstance = L.marker(e.latlng).addTo(mapInstance);
        });
    }

    async function geocodeCariLokasi() {
        const input = document.getElementById('cari-lokasi-input');
        const q = (input.value || '').trim();
        if (!q) { alert('Masukkan alamat / nama lokasi terlebih dahulu.'); return; }
        const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
        try {
            const res = await fetch(url, { headers: { 'Accept-Language': 'id' } });
            const data = await res.json();
            if (!data || data.length === 0) {
                alert('Lokasi tidak ditemukan. Coba alamat yang lebih spesifik.');
                return;
            }
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            document.querySelector('[name=lat]').value = lat.toFixed(6);
            document.querySelector('[name=lng]').value = lng.toFixed(6);
            if (markerInstance) markerInstance.setLatLng([lat, lng]);
            else markerInstance = L.marker([lat, lng]).addTo(mapInstance);
            mapInstance.setView([lat, lng], 15, { animate: true });
        } catch (e) {
            alert('Gagal mencari lokasi. Periksa koneksi internet.');
        }
    }

    // ===== Upload Foto & Video =====
    let currentFiles = [];
    async function handleFiles(files) {
        for (const file of files) {
            if (file.size > 8 * 1024 * 1024) { alert('File ' + file.name + ' terlalu besar. Maks 8MB.'); continue; }
            const isVideo = file.type.startsWith('video/');
            const url = await readFile(file);
            currentFiles.push({ name: file.name, url: url, isVideo: isVideo });
        }
        renderPreviews();
    }
    function readFile(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.readAsDataURL(file);
        });
    }
    function renderPreviews() {
        const box = document.getElementById('file-preview');
        box.innerHTML = currentFiles.map((f, i) => `
            <div class="relative aspect-video rounded-xl border border-slate-200 overflow-hidden bg-slate-100 group">
                ${f.isVideo
                    ? '<video src="' + f.url + '" class="w-full h-full object-cover"></video><span class="absolute inset-0 flex items-center justify-center text-white/80"><span class="material-symbols-outlined text-3xl bg-black/40 rounded-full p-1.5">play_circle</span></span>'
                    : '<img src="' + f.url + '" class="w-full h-full object-cover">'}
                <button type="button" onclick="hapusFile(${i})" class="absolute top-1.5 right-1.5 w-6 h-6 bg-white hover:bg-red-50 hover:text-red-600 text-slate-700 rounded-full flex items-center justify-center shadow-md transition-colors">
                    <span class="material-symbols-outlined text-[14px]">close</span>
                </button>
                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-1 text-[10px] text-white truncate">${f.name}</div>
            </div>
        `).join('');
    }
    function hapusFile(i) { currentFiles.splice(i, 1); renderPreviews(); }

    function simpanLaporan(e) {
        e.preventDefault();
        const f = document.getElementById('tambah-form');
        const all = getReports();
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const dibuat = `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}.${pad(now.getMinutes())}`;
        const kode = 'SPJ-' + String(100000 + Math.floor(Math.random() * 900000));

        const isOnline = sumber === 'online';
        const record = {
            kode: kode,
            jenis: f.jenis.value,
            sumber_asal: isOnline ? 'online' : 'offline',
            platform: isOnline ? f.platform.value : '',
            nama: isOnline ? '' : f.nama_offline.value.trim(),
            nik: isOnline ? '' : f.nik_offline.value.trim(),
            telepon: f.telepon.value.trim(),
            kategori: f.kategori ? f.kategori.value : '',
            deskripsi: f.deskripsi.value.trim(),
            lokasi: f.lokasi.value.trim(),
            lat: f.lat.value,
            lng: f.lng.value,
            files: currentFiles.map(x => ({ name: x.name, url: x.url, isVideo: x.isVideo })),
            dibuat: dibuat,
            statusIdx: 0,
        };
        all.unshift(record);
        saveAll(all);
        renderList();
        tutupTambah();
        tampilToast('Berhasil menambah data laporan baru (' + kode + ').');
    }




    setTab('semua');
</script>
@endpush
