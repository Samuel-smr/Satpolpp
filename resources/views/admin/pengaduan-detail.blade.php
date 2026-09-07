@extends('layouts.admin')

@section('title', 'Detail Pengaduan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@section('content')
    <div x-data="{}">
        <a href="{{ route('admin.pengaduan') }}" class="inline-flex items-center gap-2 text-sidebar-active font-medium hover:underline mb-6">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span>Kembali ke daftar pengaduan</span>
        </a>

        {{-- Placeholder saat data belum dimuat / tidak ditemukan --}}
        <div id="notfound" class="hidden bg-white border border-slate-200/80 rounded-2xl p-12 text-center shadow-sm">
            <div class="text-5xl mb-3">🔍</div>
            <h2 class="font-headline text-lg font-bold text-slate-800 mb-1">Laporan tidak ditemukan</h2>
            <p class="text-sm text-slate-500 mb-6">Data laporan mungkin sudah dihapus atau kode tidak tersedia.</p>
            <a href="{{ route('admin.pengaduan') }}" class="inline-flex items-center gap-2 bg-sidebar-active hover:bg-sidebar-hover text-white px-5 py-2.5 rounded-xl font-medium transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke daftar
            </a>
        </div>

        <div id="detail" class="hidden">
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
                                <span class="text-slate-400">Sumber:</span>
                                <span class="font-medium" id="d-sumber">--</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Identitas:</span>
                                <span class="font-medium" id="d-identitas">--</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Nomor Telepon:</span>
                                <span class="font-medium" id="d-telepon">--</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Koordinat:</span>
                                <span class="font-medium" id="d-koordinat">--</span>
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

                        {{-- Lampiran Foto & Video --}}
                        <div id="d-files-wrap" class="hidden mb-6">
                            <h3 class="font-headline font-bold text-slate-800 mb-3">Lampiran Foto &amp; Video</h3>
                            <div id="d-files" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                        </div>

                        {{-- Foto Bukti Penanganan --}}
                        <div id="d-bukti-selesai-wrap" class="hidden mb-6">
                            <h3 class="font-headline font-bold text-slate-800 mb-3">
                                <span class="inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-emerald-600">verified</span>
                                    Foto Bukti Hasil Penanganan
                                </span>
                            </h3>
                            <div id="d-bukti-selesai" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                            <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan Hasil Penanganan</p>
                                <p class="text-sm text-slate-700" id="d-bukti-keterangan">--</p>
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
                            <button type="button" onclick="majuStatus(1)" class="w-full bg-sidebar-active hover:bg-sidebar-hover text-white py-2.5 rounded-xl font-medium transition-colors">Tandai Diterima</button>
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
    </div>

    {{-- Modal konfirmasi penyelesaian + upload foto bukti penanganan --}}
    <div id="modal-bukti" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModalBukti()"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800">Selesaikan Laporan</h2>
                <button type="button" onclick="tutupModalBukti()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-emerald-700">
                    <span class="material-symbols-outlined">info</span>
                    <p class="text-sm">
                        Untuk menandai laporan <strong id="b-kode">--</strong> sebagai <strong>selesai</strong>,
                        Anda wajib mengunggah minimal 1 foto bukti, mengisi keterangan tindakan,
                        <strong>serta menandai titik lokasi kejadian</strong> di peta.
                        Lokasi penandaan hanya digunakan untuk peta persebaran petugas.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Foto Bukti Hasil Penanganan <span class="text-red-500">*</span></label>
                    <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-xl p-6 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-50/50 transition-colors">
                        <input type="file" id="bukti-input" multiple accept="image/*" class="absolute opacity-0 w-0 h-0" onchange="handleBuktiFiles(this.files)">
                        <span class="material-symbols-outlined text-3xl text-emerald-500">add_a_photo</span>
                        <p class="text-sm font-medium text-slate-600 mt-1">Klik untuk pilih foto bukti</p>
                        <p class="text-xs text-slate-400 mt-0.5">Hanya foto (JPG, PNG), maks 8MB per file</p>
                    </label>
                    <div id="bukti-preview" class="mt-3 grid grid-cols-3 gap-3"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Keterangan Hasil Penanganan <span class="text-red-500">*</span></label>
                    <textarea id="bukti-keterangan" rows="3" placeholder="Jelaskan tindakan yang telah dilakukan untuk menuntaskan laporan ini..."
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 outline-none resize-y"></textarea>
                </div>

                {{-- Peta: tanda lokasi kejadian --}}
                <div>
                    <label class="block text-sm font-medium mb-2">Titik Lokasi Kejadian <span class="text-red-500">*</span></label>
                    <p class="text-xs text-slate-400 mb-2">Klik peta untuk menandai lokasi kejadian perkara.</p>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Latitude</label>
                            <input type="text" id="lok-lat" readonly placeholder="Belum dipilih"
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Longitude</label>
                            <input type="text" id="lok-lng" readonly placeholder="Belum dipilih"
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                        </div>
                    </div>
                    <div class="flex gap-2 mb-3">
                        <div class="flex-1 relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                            <input type="text" id="lok-search" placeholder="Cari lokasi / alamat..." onkeydown="if(event.key==='Enter'){event.preventDefault();geocodeLokasi();}"
                                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>
                        <button type="button" onclick="geocodeLokasi()" class="inline-flex items-center justify-center gap-1.5 bg-sidebar-active hover:bg-sidebar-hover text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-colors">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                            Cari
                        </button>
                    </div>
                    <div class="relative">
                        <div id="lokasi-picker" class="w-full h-64 bg-slate-100 rounded-xl border border-slate-200 overflow-hidden z-10"></div>
                        <span class="absolute top-3 right-3 z-[1000] bg-white/90 backdrop-blur text-xs font-medium px-3 py-1.5 rounded-lg shadow-sm border border-slate-200 text-slate-600">
                            Klik peta untuk tandai titik
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100">
                <button type="button" onclick="tutupModalBukti()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                <button type="button" onclick="konfirmasiSelesai()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-xl font-medium transition-colors">Simpan &amp; Selesaikan</button>
            </div>
        </div>
    </div>



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
    const CURRENT_KODE = @json($kode);

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
    function badgeClass(idx) {
        if (idx >= 5) return 'bg-emerald-100 text-emerald-700';
        if (idx >= 3) return 'bg-amber-100 text-amber-700';
        if (idx >= 1) return 'bg-blue-100 text-blue-700';
        return 'bg-slate-100 text-slate-600';
    }

    function renderDetail() {
        const r = getReports().find(x => x.kode === CURRENT_KODE);
        if (!r) {
            document.getElementById('notfound').classList.remove('hidden');
            return;
        }
        document.getElementById('detail').classList.remove('hidden');
        const idx = r.statusIdx != null ? r.statusIdx : 0;

        document.getElementById('d-kode').textContent = r.kode;
        document.getElementById('d-badge').textContent = labelStatus(idx);
        document.getElementById('d-badge').className = 'text-sm font-semibold px-3 py-1 rounded-full ' + badgeClass(idx);
        document.getElementById('d-jenis').textContent = labelJenis(r.jenis);
        document.getElementById('d-kategori').textContent = r.kategori || '-';
        document.getElementById('d-sumber').textContent = (r.sumber_asal === 'offline') ? 'Offline (Langsung)' : 'Online (' + (r.platform || 'Medsos') + ')';
        document.getElementById('d-identitas').textContent = (r.sumber_asal === 'offline') ? (r.nama + (r.nik ? ' • ' + r.nik : '')) : '-';
        document.getElementById('d-telepon').textContent = r.telepon || '-';
        document.getElementById('d-koordinat').textContent = (r.lat && r.lng) ? r.lat + ', ' + r.lng : '-';
        document.getElementById('d-lokasi').textContent = r.lokasi || '-';
        document.getElementById('d-waktu').textContent = r.dibuat || r.waktu || '-';
        document.getElementById('d-deskripsi').textContent = r.deskripsi || '-';

        const filesWrap = document.getElementById('d-files-wrap');
        const filesBox = document.getElementById('d-files');
        if (r.files && r.files.length > 0) {
            filesWrap.classList.remove('hidden');
            filesBox.innerHTML = r.files.map(f => `
                <figure class="${f.isVideo ? 'aspect-video' : 'aspect-square'} rounded-xl overflow-hidden border border-slate-200 bg-slate-100 relative group">
                    ${f.isVideo ? '<video src="' + f.url + '" class="w-full h-full object-cover"></video><span class="absolute inset-0 flex items-center justify-center text-white/80"><span class="material-symbols-outlined text-3xl bg-black/40 rounded-full p-1.5">play_circle</span></span>' : '<img src="' + f.url + '" class="w-full h-full object-cover">'}
                    <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-1 text-[10px] text-white truncate">${f.name}</figcaption>
                </figure>
            `).join('');
        } else {
            filesWrap.classList.add('hidden');
            filesBox.innerHTML = '';
        }

        const buktiWrap = document.getElementById('d-bukti-selesai-wrap');
        const buktiBox = document.getElementById('d-bukti-selesai');
        const buktiKet = document.getElementById('d-bukti-keterangan');
        if (r.buktiSelesai && r.buktiSelesai.length > 0) {
            buktiWrap.classList.remove('hidden');
            buktiBox.innerHTML = r.buktiSelesai.map(f => `
                <figure class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 relative group">
                    <img src="${f.url}" class="w-full h-full object-cover" alt="${f.name || 'Bukti penanganan'}">
                    <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-1 text-[10px] text-white truncate">${f.name || 'Bukti'}</figcaption>
                </figure>
            `).join('');
            buktiKet.textContent = r.keteranganSelesai || '-';
        } else {
            buktiWrap.classList.add('hidden');
            buktiBox.innerHTML = '';
            buktiKet.textContent = '';
        }

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

        window.scrollTo(0, 0);
    }

    function majuStatus(newIdx) {
        const all = getReports();
        const rec = all.find(x => x.kode === CURRENT_KODE);
        if (!rec) return;
        const cur = rec.statusIdx != null ? rec.statusIdx : 0;
        if (newIdx <= cur) { alert('Status tidak bisa mundur.'); return; }

        if (newIdx >= 5) {
            bukaModalBukti(rec);
            return;
        }

        rec.statusIdx = newIdx;
        saveAll(all);
        renderDetail();
        tampilToast('Edit berhasil! Status diperbarui menjadi: ' + labelStatus(newIdx));
    }

    function hapusLaporan() {
        window.konfirmasiHapus({
            title: 'Hapus Laporan',
            message: 'Apakah Anda yakin ingin menghapus laporan ' + CURRENT_KODE + '? Data yang dihapus tidak dapat dipulihkan.',
            onConfirm: () => {
                const all = getReports().filter(x => x.kode !== CURRENT_KODE);
                saveAll(all);
                tampilToast('Laporan berhasil dihapus.');
                setTimeout(() => window.location.href = '{{ route('admin.pengaduan') }}', 600);
            }
        });
    }

    // ===== Foto bukti hasil penanganan (wajib saat selesaikan) =====
    let buktiFiles = [];
    let lokasiMap = null;
    let lokasiMarker = null;
    let lokasiLat = '';
    let lokasiLng = '';

    function initLokasiPicker() {
        if (lokasiMap) { lokasiMap.remove(); lokasiMap = null; lokasiMarker = null; }
        lokasiMap = L.map('lokasi-picker').setView([-7.7971, 110.1646], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(lokasiMap);
        lokasiMap.on('click', function (e) {
            lokasiLat = e.latlng.lat.toFixed(6);
            lokasiLng = e.latlng.lng.toFixed(6);
            document.getElementById('lok-lat').value = lokasiLat;
            document.getElementById('lok-lng').value = lokasiLng;
            if (lokasiMarker) lokasiMarker.setLatLng(e.latlng);
            else lokasiMarker = L.marker(e.latlng).addTo(lokasiMap);
        });
        setTimeout(() => { if (lokasiMap) lokasiMap.invalidateSize(); }, 50);
    }

    async function geocodeLokasi() {
        const input = document.getElementById('lok-search');
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
            lokasiLat = lat.toFixed(6);
            lokasiLng = lng.toFixed(6);
            document.getElementById('lok-lat').value = lokasiLat;
            document.getElementById('lok-lng').value = lokasiLng;
            if (lokasiMarker) lokasiMarker.setLatLng([lat, lng]);
            else lokasiMarker = L.marker([lat, lng]).addTo(lokasiMap);
            lokasiMap.setView([lat, lng], 15, { animate: true });
        } catch (e) {
            alert('Gagal mencari lokasi. Periksa koneksi internet.');
        }
    }

    function bukaModalBukti(r) {
        document.getElementById('b-kode').textContent = r.kode;
        buktiFiles = [];
        document.getElementById('bukti-keterangan').value = '';
        lokasiLat = '';
        lokasiLng = '';
        document.getElementById('lok-lat').value = '';
        document.getElementById('lok-lng').value = '';
        renderBuktiPreviews();
        document.getElementById('modal-bukti').classList.remove('hidden');
        initLokasiPicker();
    }
    function tutupModalBukti() {
        document.getElementById('modal-bukti').classList.add('hidden');
        buktiFiles = [];
        renderBuktiPreviews();
        if (lokasiMap) { lokasiMap.remove(); lokasiMap = null; lokasiMarker = null; }
        lokasiLat = '';
        lokasiLng = '';
    }
    async function handleBuktiFiles(files) {
        for (const file of files) {
            if (file.size > 8 * 1024 * 1024) { alert('File ' + file.name + ' terlalu besar. Maks 8MB.'); continue; }
            if (!file.type.startsWith('image/')) { alert('Hanya file gambar yang diperbolehkan.'); continue; }
            const url = await readFile(file);
            buktiFiles.push({ name: file.name, url: url });
        }
        renderBuktiPreviews();
    }
    function readFile(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.readAsDataURL(file);
        });
    }
    function renderBuktiPreviews() {
        const box = document.getElementById('bukti-preview');
        box.innerHTML = buktiFiles.map((f, i) => `
            <div class="relative aspect-square rounded-xl border border-slate-200 overflow-hidden bg-slate-100 group">
                <img src="${f.url}" class="w-full h-full object-cover" alt="${f.name}">
                <button type="button" onclick="hapusBuktiFile(${i})" class="absolute top-1.5 right-1.5 w-6 h-6 bg-white hover:bg-red-50 hover:text-red-600 text-slate-700 rounded-full flex items-center justify-center shadow-md transition-colors">
                    <span class="material-symbols-outlined text-[14px]">close</span>
                </button>
            </div>
        `).join('');
    }
    function hapusBuktiFile(i) { buktiFiles.splice(i, 1); renderBuktiPreviews(); }

    function konfirmasiSelesai() {
        if (buktiFiles.length === 0) {
            alert('Wajib mengunggah minimal 1 foto bukti hasil penanganan.');
            return;
        }
        const keterangan = document.getElementById('bukti-keterangan').value.trim();
        if (!keterangan) {
            alert('Wajib mengisi keterangan hasil penanganan.');
            document.getElementById('bukti-keterangan').focus();
            return;
        }
        if (!lokasiLat || !lokasiLng) {
            alert('Wajib menandai titik lokasi kejadian di peta.');
            return;
        }
        const all = getReports();
        const rec = all.find(x => x.kode === CURRENT_KODE);
        if (!rec) return;
        rec.statusIdx = 5;
        rec.buktiSelesai = buktiFiles.map(x => ({ name: x.name, url: x.url }));
        rec.keteranganSelesai = keterangan;
        rec.lokasiSelesai = { lat: lokasiLat, lng: lokasiLng };
        saveAll(all);
        tutupModalBukti();
        renderDetail();
        tampilToast('Edit berhasil! Laporan diselesaikan dengan bukti penanganan.');
    }




    renderDetail();
</script>
@endpush
