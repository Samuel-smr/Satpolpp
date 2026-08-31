@extends('layouts.pelapor')

@section('title', 'Cek Status Laporan')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Cek Status Laporan</h1>
            <p class="mt-2 text-slate-500">Berikut daftar laporan yang pernah Anda laporkan. Klik untuk melihat perkembangannya.</p>
        </div>

        {{-- Daftar laporan --}}
        <div id="report-list" class="space-y-4 mb-8"></div>

        {{-- Kosong --}}
        <div id="empty-state" class="hidden bg-white border border-slate-200 rounded-xl p-10 text-center shadow-sm">
            <div class="text-5xl mb-4">📭</div>
            <h2 class="font-headline font-bold text-lg text-slate-800">Belum Ada Laporan</h2>
            <p class="mt-2 text-slate-500">Anda belum pernah membuat laporan.</p>
            <a href="{{ route('pelapor.lapor') }}" class="inline-block mt-5 bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-700 transition-colors">Buat Laporan Sekarang</a>
        </div>

        {{-- Detail & tracking --}}
        <div id="detail" class="hidden">
            <button type="button" onclick="backToList()" class="mb-4 inline-flex items-center gap-2 text-brand-600 font-medium hover:underline">
                ← <span>Kembali ke daftar</span>
            </button>

            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                {{-- Header laporan --}}
                <div class="flex items-center justify-between gap-4 mb-5">
                    <h2 class="font-headline font-bold text-lg text-slate-800" id="d-kode">--</h2>
                    <span class="text-sm font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full" id="d-badge">--</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
                    <div>
                        <span class="text-slate-400">Jenis:</span>
                        <span class="font-medium" id="d-jenis">--</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Kategori:</span>
                        <span class="font-medium" id="d-kategori">--</span>
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

                {{-- Tracking --}}
                <h3 class="font-headline font-bold text-slate-800 mb-5">Pelacakan Laporan</h3>
                <ol class="relative ml-1" id="tracking-list"></ol>

                {{-- Bukti --}}
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <h3 class="font-headline font-bold text-slate-800 mb-3">📸 Bukti Penanganan</h3>
                    <p class="text-sm text-slate-500 mb-4">Dokumentasi dari petugas sebagai bukti laporan telah ditangani.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="bukti-list">
                        <figure class="relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ asset('images/bukti-satpol.jpg') }}" alt="Bukti penanganan laporan" class="w-full h-full object-cover">
                            <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-xs text-white">Bukti penanganan 1</figcaption>
                        </figure>
                        <figure class="relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ asset('images/bukti-satpol.jpg') }}" alt="Bukti penanganan laporan" class="w-full h-full object-cover">
                            <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-xs text-white">Bukti penanganan 2</figcaption>
                        </figure>
                    </div>
                </div>

                {{-- Penilaian kinerja (muncul saat laporan selesai) --}}
                <div id="penilaian-section" class="hidden mt-8 border-t border-slate-200 pt-6">
                    <h3 class="font-headline font-bold text-slate-800 mb-1">Bagaimana kepuasan Anda terhadap layanan kami?</h3>
                    <p class="text-sm text-slate-500 mb-5">Beri penilaian untuk kinerja Satpol PP dalam menangani laporan Anda.</p>

                    <div id="rating-view">
                        <div class="flex gap-2 text-4xl justify-center" id="rating-stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="rating-star grayscale hover:scale-110 transition-transform" data-value="{{ $i }}">⭐</button>
                            @endfor
                        </div>

                        <div class="mt-5">
                            <label class="block text-sm font-medium mb-1">Komentar / Saran</label>
                            <textarea id="rating-komentar" rows="3" placeholder="Tulis komentar atau saran Anda..."
                                      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"></textarea>
                        </div>

                        <button type="button" onclick="simpanPenilaian()" class="mt-4 w-full bg-brand-600 text-white py-3 rounded-lg font-medium hover:bg-brand-700 transition-colors">Kirim Penilaian</button>
                    </div>

                    <div id="rating-sukses" class="hidden text-center py-4">
                        <div class="text-5xl mb-3">🙏</div>
                        <h3 class="font-headline font-bold text-slate-800 mb-1">Terima Kasih!</h3>
                        <p class="text-sm text-slate-500">Penilaian Anda telah kami terima sebagai bahan evaluasi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const STATUSES = [
        ['Laporan dibuat', 'Laporan Anda telah kami terima.'],
        ['Laporan diterima', 'Tim kami telah menerima laporan.'],
        ['Sedang diverifikasi', 'Sedang memeriksa kebenaran laporan.'],
        ['Ditindaklanjuti', 'Laporan sedang ditangani petugas.'],
        ['Penanganan selesai', 'Penanganan di lokasi selesai.'],
        ['Laporan selesai', 'Laporan telah selesai ditangani.'],
    ];

    // Ambil atau seed data contoh
    function getReports() {
        let reports = JSON.parse(localStorage.getItem('sipraja_reports') || '[]');
        if (reports.length === 0) {
            reports = [
                { kode: 'SPJ-584021', jenis: 'darurat', kategori: 'Ketertiban Umum', deskripsi: 'Kerumunan massa di alun-alun yang berpotensi gangguan.', lokasi: 'Alun-alun Kulon Progo', telepon: '081234567890', nik: '', dibuat: '31/08/2026 09.12', statusIdx: 3 },
                { kode: 'SPJ-583990', jenis: 'informasi', kategori: 'Kebersihan Lingkungan', deskripsi: 'Tumpukan sampah di pinggir jalan utama.', lokasi: 'Jl. Sugiman, Pengasih', telepon: '082134567890', nik: '', dibuat: '30/08/2026 16.45', statusIdx: 5 },
                { kode: 'SPJ-583772', jenis: 'informasi', kategori: 'Keamanan', deskripsi: 'Orang mabuk berkeliaran di area pasar.', lokasi: 'Pasar Wates', telepon: '083145678901', nik: '', dibuat: '29/08/2026 20.03', statusIdx: 1 },
            ];
            localStorage.setItem('sipraja_reports', JSON.stringify(reports));
        }
        return reports;
    }

    function labelJenis(j) { return j === 'darurat' ? 'Darurat' : 'Informasi & Pengaduan'; }
    function labelStatus(idx) { return STATUSES[idx] ? STATUSES[idx][0] : 'Dikirim'; }
    function badgeClass(idx) {
        if (idx >= 5) return 'bg-emerald-100 text-emerald-700';
        if (idx >= 3) return 'bg-amber-100 text-amber-700';
        if (idx >= 1) return 'bg-blue-100 text-blue-700';
        return 'bg-slate-100 text-slate-600';
    }
    function jenisClass(j) {
        return j === 'darurat' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700';
    }

    function renderList() {
        const reports = getReports();
        const list = document.getElementById('report-list');
        const empty = document.getElementById('empty-state');

        if (reports.length === 0) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        list.innerHTML = reports.map((r, i) => `
            <button type="button" onclick="openDetail(${i})"
                class="w-full text-left bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-brand-300 transition-all">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full ${jenisClass(r.jenis)}">${labelJenis(r.jenis)}</span>
                            <span class="text-xs text-slate-400">${r.waktu ? (r.dibuat || r.waktu) : ''}</span>
                        </div>
                        <h3 class="font-headline font-bold text-slate-800">${r.kategori || 'Tanpa kategori'}</h3>
                        <p class="mt-1 text-sm text-slate-500 line-clamp-2">${r.deskripsi || '-'}</p>
                        <p class="mt-1 text-xs text-slate-400">${r.kode}</p>
                    </div>
                    <span class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${badgeClass(r.statusIdx)}">${labelStatus(r.statusIdx)}</span>
                </div>
            </button>
        `).join('');
    }

    function openDetail(index) {
        const reports = getReports();
        const r = reports[index];
        const idx = r.statusIdx != null ? r.statusIdx : 0;

        document.getElementById('d-kode').textContent = r.kode;
        document.getElementById('d-badge').textContent = labelStatus(idx);
        document.getElementById('d-badge').className = 'text-sm font-semibold px-3 py-1 rounded-full ' + badgeClass(idx);
        document.getElementById('d-jenis').textContent = labelJenis(r.jenis);
        document.getElementById('d-kategori').textContent = r.kategori || '-';
        document.getElementById('d-lokasi').textContent = r.lokasi || '-';
        document.getElementById('d-waktu').textContent = r.dibuat || r.waktu || '-';
        document.getElementById('d-deskripsi').textContent = r.deskripsi || '-';

        // Tracking list (presisi: garis & titik sejajar di x tengah)
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

        document.getElementById('report-list').classList.add('hidden');
        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('detail').classList.remove('hidden');

        // Penilaian kinerja hanya jika laporan selesai
        currentKode = r.kode;
        const section = document.getElementById('penilaian-section');
        section.classList.toggle('hidden', !(idx >= 5));

        if (idx >= 5) {
            // Dummy: selalu tampilkan form rating fresh setiap kali dibuka
            document.getElementById('rating-view').classList.remove('hidden');
            document.getElementById('rating-sukses').classList.add('hidden');
            document.getElementById('rating-komentar').value = '';
            ratingPicked = 0;
            ratingRender(0);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function backToList() {
        document.getElementById('detail').classList.add('hidden');
        document.getElementById('report-list').classList.remove('hidden');
        renderList();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ----- Penilaian kinerja -----
    let currentKode = null;
    let ratingPicked = 0;

    function ratingRender(hover) {
        const active = hover || ratingPicked;
        document.querySelectorAll('.rating-star').forEach(s => {
            s.classList.toggle('grayscale', +s.dataset.value > active);
        });
    }

    function simpanPenilaian() {
        if (!ratingPicked) { alert('Silakan pilih penilaian bintang terlebih dahulu.'); return; }
        // Dummy: penilaian tidak disimpan ke penyimpanan, hanya menampilkan konfirmasi.
        // Saat halaman di-refresh, form rating dapat diisi kembali.
        document.getElementById('rating-view').classList.add('hidden');
        document.getElementById('rating-sukses').classList.remove('hidden');
    }

    // Rating stars: hover preview + click pilih
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('mouseenter', () => ratingRender(+star.dataset.value));
        star.addEventListener('mouseleave', () => ratingRender(0));
        star.addEventListener('click', () => {
            ratingPicked = +star.dataset.value;
            ratingRender(0);
        });
    });

    renderList();
</script>
@endpush
