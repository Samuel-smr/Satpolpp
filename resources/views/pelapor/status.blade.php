@extends('layouts.pelapor')

@section('title', 'Cek Status Laporan')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-headline text-2xl md:text-3xl font-bold text-slate-800">Cek Status Laporan</h1>
            <p class="mt-2 text-slate-500">Berikut daftar laporan yang pernah Anda laporkan. Klik untuk melihat perkembangannya.</p>
        </div>

        {{-- Pencarian --}}
        <div class="relative mb-6">
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
            <input type="text" id="search-input" placeholder="Cari kode, lokasi, atau deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-sm">
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

        {{-- Hasil pencarian tidak ditemukan --}}
        <div id="empty-search" class="hidden bg-white border border-slate-200 rounded-xl p-10 text-center shadow-sm">
            <div class="text-5xl mb-4">🔍</div>
            <h2 class="font-headline font-bold text-lg text-slate-800">Laporan Tidak Ditemukan</h2>
            <p class="mt-2 text-slate-500">Tidak ada laporan yang cocok dengan kata kunci pencarian Anda.</p>
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
                <div id="bukti-section" class="hidden mt-8 border-t border-slate-200 pt-6">
                    <h3 class="font-headline font-bold text-slate-800 mb-3">📸 Bukti Penanganan</h3>
                    <p class="text-sm text-slate-500 mb-4">Dokumentasi dari petugas sebagai bukti laporan telah ditangani.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="bukti-list"></div>
                    <div id="bukti-keterangan" class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan Hasil Penanganan</p>
                        <p class="text-sm text-slate-700" id="bukti-keterangan-text">--</p>
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

    const reportsFromDatabase = @json($reports);

    function getReports() {
        return reportsFromDatabase;
    }

    function labelStatus(idx) { return STATUSES[idx] ? STATUSES[idx][0] : 'Dikirim'; }
    function badgeClass(idx) {
        if (idx >= 5) return 'bg-emerald-100 text-emerald-700';
        if (idx >= 3) return 'bg-amber-100 text-amber-700';
        if (idx >= 1) return 'bg-blue-100 text-blue-700';
        return 'bg-slate-100 text-slate-600';
    }
    function filterReports() {
        const q = (document.getElementById('search-input').value || '').trim().toLowerCase();
        const all = getReports();
        if (!q) return all;
        return all.filter(r => {
            const haystack = [r.kode, r.lokasi, r.deskripsi, r.telepon].join(' ').toLowerCase();
            return haystack.includes(q);
        });
    }

    function renderList() {
        const reports = displayedReports = filterReports();
        const list = document.getElementById('report-list');
        const empty = document.getElementById('empty-state');
        const emptySearch = document.getElementById('empty-search');

        if (getReports().length === 0) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            emptySearch.classList.add('hidden');
            return;
        }
        empty.classList.add('hidden');

        if (reports.length === 0) {
            list.innerHTML = '';
            emptySearch.classList.remove('hidden');
            return;
        }
        emptySearch.classList.add('hidden');

        list.innerHTML = reports.map((r, i) => `
            <button type="button" onclick="openDetail(${i})"
                class="w-full text-left bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-brand-300 transition-all">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-slate-400">${escapeHtml(r.waktu ? (r.dibuat || r.waktu) : '')}</span>
                        </div>
                        <h3 class="font-headline font-bold text-slate-800">${escapeHtml(r.lokasi || 'Laporan')}</h3>
                        <p class="mt-1 text-sm text-slate-500 line-clamp-2">${escapeHtml(r.deskripsi || '-')}</p>
                        <p class="mt-1 text-xs text-slate-400">${escapeHtml(r.kode)}</p>
                    </div>
                    <span class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${badgeClass(r.statusIdx)}">${labelStatus(r.statusIdx)}</span>
                </div>
            </button>
        `).join('');
    }

    let displayedReports = [];
    function openDetail(index) {
        const r = displayedReports[index];
        const idx = r.statusIdx != null ? r.statusIdx : 0;

        document.getElementById('d-kode').textContent = r.kode;
        document.getElementById('d-badge').textContent = labelStatus(idx);
        document.getElementById('d-badge').className = 'text-sm font-semibold px-3 py-1 rounded-full ' + badgeClass(idx);
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
            if (r.feedbackSubmitted) {
                document.getElementById('rating-view').classList.add('hidden');
                document.getElementById('rating-sukses').classList.remove('hidden');
            } else {
                document.getElementById('rating-view').classList.remove('hidden');
                document.getElementById('rating-sukses').classList.add('hidden');
                document.getElementById('rating-komentar').value = '';
                ratingPicked = 0;
                ratingRender(0);
            }
        }

        // Bukti penanganan (foto + keterangan) — tanpa lokasi penandaan admin
        const buktiSection = document.getElementById('bukti-section');
        const buktiList = document.getElementById('bukti-list');
        const adaBukti = r.buktiSelesai && r.buktiSelesai.length > 0;
        if (idx >= 5 && adaBukti) {
            buktiSection.classList.remove('hidden');
            buktiList.innerHTML = r.buktiSelesai.map((f, i) => `
                <figure class="relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                    <img src="${escapeHtml(f.url)}" alt="Bukti penanganan ${i + 1}" class="w-full h-full object-cover">
                    <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-xs text-white">Bukti penanganan ${i + 1}</figcaption>
                </figure>
            `).join('');
            document.getElementById('bukti-keterangan-text').textContent = r.keteranganSelesai || '-';
        } else {
            buktiSection.classList.add('hidden');
            buktiList.innerHTML = '';
            document.getElementById('bukti-keterangan-text').textContent = '';
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function backToList() {
        document.getElementById('detail').classList.add('hidden');
        document.getElementById('report-list').classList.remove('hidden');
        renderList();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Search: filter daftar saat mengetik
    document.getElementById('search-input').addEventListener('input', () => {
        if (!document.getElementById('detail').classList.contains('hidden')) {
            document.getElementById('report-list').classList.remove('hidden');
            document.getElementById('detail').classList.add('hidden');
        }
        renderList();
    });

    // ----- Penilaian kinerja -----
    let currentKode = null;
    let ratingPicked = 0;

    function ratingRender(hover) {
        const active = hover || ratingPicked;
        document.querySelectorAll('.rating-star').forEach(s => {
            s.classList.toggle('grayscale', +s.dataset.value > active);
        });
    }

    async function simpanPenilaian() {
        if (!ratingPicked) { alert('Silakan pilih penilaian bintang terlebih dahulu.'); return; }
        const report = getReports().find(item => item.kode === currentKode);
        if (!report?.feedbackUrl) { alert('Laporan tidak dapat ditemukan.'); return; }
        const button = document.querySelector('[onclick="simpanPenilaian()"]');
        button.disabled = true;
        try {
            const response = await fetch(report.feedbackUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    rating: ratingPicked,
                    comment: document.getElementById('rating-komentar').value,
                }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Feedback gagal disimpan.');
            report.feedbackSubmitted = true;
            report.feedbackRating = ratingPicked;
            report.feedbackComment = document.getElementById('rating-komentar').value;
            document.getElementById('rating-view').classList.add('hidden');
            document.getElementById('rating-sukses').classList.remove('hidden');
        } catch (error) {
            alert(error.message);
        } finally {
            button.disabled = false;
        }
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
