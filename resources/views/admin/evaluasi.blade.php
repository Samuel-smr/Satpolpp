@extends('layouts.admin')

@section('title', 'Evaluasi Kegiatan')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Evaluasi Kegiatan</h1>
            <p class="text-sm text-slate-500 mt-1">Catat hasil dan kendala dari setiap kegiatan, baik internal Satpol PP
                maupun kegiatan dari laporan (eksternal).</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 bg-sidebar-active text-white px-5 py-2.5 rounded-xl font-medium hover:bg-sidebar-hover transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Evaluasi
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-sidebar-active/10 text-sidebar-active flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">assessment</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total Evaluasi</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">home_work</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Internal</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-internal">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">description</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Eksternal (Laporan)</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-eksternal">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">warning</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Ada Kendala</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-kendala">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Daftar Evaluasi Kegiatan</span>
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full font-medium">Data
                Dummy + Tambahan Admin</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Kegiatan</th>
                        <th class="px-6 py-3 font-semibold">Sumber</th>
                        <th class="px-6 py-3 font-semibold">Keterangan / Kendala</th>
                        <th class="px-6 py-3 font-semibold">Dibuat</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="evaluasi-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">📊</div>
            <p>Belum ada data evaluasi kegiatan.</p>
        </div>
    </div>

    {{-- Modal tambah/ubah evaluasi --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800" id="modal-judul">Tambah Evaluasi</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="evaluasi-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanEvaluasi(event)">
                <input type="hidden" name="edit_index" value="">
                <div>
                    <label class="block text-sm font-medium mb-1">Sumber <span class="text-red-500">*</span></label>
                    <select name="sumber" id="select-sumber" onchange="loadOptions()"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none bg-white">
                        <option value="">Pilih Sumber</option>
                        <option value="Internal">Internal (Kegiatan Satpol PP)</option>
                        <option value="Eksternal">Eksternal (Dari Laporan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" id="select-kategori"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none bg-white">
                        <option value="" disabled selected>Pilih Kategori...</option>
                        <option value="Patroli ketertiban umum, ketentraman, dan perlindungan masyarakat">Patroli ketertiban
                            umum, ketentraman, dan perlindungan masyarakat</option>
                        <option value="Pengamanan dan pengawalan pejabat daerah">Pengamanan dan pengawalan pejabat daerah
                        </option>
                        <option value="Pengamanan acara atau event">Pengamanan acara atau event</option>
                        <option value="Penanganan pengaduan pelanggaran ketertiban umum/masyarakat">Penanganan pengaduan
                            pelanggaran ketertiban umum/masyarakat</option>
                        <option value="Pembinaan dan pengawasan kepatuhan terhadap Perda dan Perkada">Pembinaan dan
                            pengawasan kepatuhan terhadap Perda dan Perkada</option>
                        <option value="Pembinaan dan pengawasan atribut">Pembinaan dan pengawasan atribut</option>
                        <option value="Penegakan peraturan daerah dan peraturan kepala daerah yustisi">Penegakan peraturan
                            daerah dan peraturan kepala daerah yustisi</option>
                        <option value="Penegakan peraturan daerah dan peraturan kepala daerah non yustisi">Penegakan
                            peraturan daerah dan peraturan kepala daerah non yustisi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <select name="nama" required id="select-kegiatan"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none bg-white">
                        <option value="">Pilih Sumber Terlebih Dahulu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Keterangan / Kendala</label>
                    <textarea name="keterangan" rows="4"
                        placeholder="Catat hasil kegiatan dan kendala yang dihadapi, mis. jumlah personel terbatas, koordinasi lintas sektor..."
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupModal()"
                        class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="bg-sidebar-active text-white px-6 py-2 rounded-xl font-medium hover:bg-sidebar-hover transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        const KODE = 'sipraja_evaluasi';

        function getData() {
            let data = JSON.parse(localStorage.getItem(KODE) || '[]');
            if (data.length === 0) {
                data = [
                    { nama: 'Patroli rutin kawasan Pasar Wates', sumber: 'Internal', kategori: 'Patroli ketertiban umum, ketentraman, dan perlindungan masyarakat', keterangan: 'Jumlah personel terbatas saat jam ramai.', dibuat: '01/09/2026' },
                    { nama: 'Penanganan laporan kerumunan di alun-alun', sumber: 'Eksternal', kategori: 'Penanganan pengaduan pelanggaran ketertiban umum/masyarakat', keterangan: 'Koordinasi dengan aparat desa membutuhkan waktu lebih.', dibuat: '02/09/2026' },
                    { nama: 'Pengamanan acara pasar malam', sumber: 'Internal', kategori: 'Pengamanan acara atau event', keterangan: 'Berjalan lancar tanpa kendala.', dibuat: '28/08/2026' },
                    { nama: 'Tindak lanjut laporan PKL liar', sumber: 'Eksternal', kategori: 'Pembinaan dan pengawasan kepatuhan terhadap Perda dan Perkada', keterangan: 'Butuh sosialisasi berulang kepada pedagang.', dibuat: '27/08/2026' },
                ];
                localStorage.setItem(KODE, JSON.stringify(data));
            }
            return data;
        }



        function sumberClass(s) {
            return s === 'Internal'
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-blue-50 text-blue-700 border-blue-200';
        }

        function ambilKegiatanInternal() {
            try {
                const k = JSON.parse(localStorage.getItem('sipraja_kegiatan') || '[]');
                return Array.isArray(k) ? k : [];
            } catch (e) {
                return [];
            }
        }

        function ambilLaporanSelesai() {
            try {
                const r = JSON.parse(localStorage.getItem('sipraja_reports') || '[]');
                return Array.isArray(r) ? r.filter(x => (x.statusIdx || 0) >= 5) : [];
            } catch (e) {
                return [];
            }
        }

        function loadOptions() {
            const sel = document.getElementById('select-kegiatan');
            const kat = document.getElementById('select-kategori');
            const src = document.getElementById('select-sumber').value;

            sel.innerHTML = '';

           // 2. Logic kontrol field Kategori
            if (src === 'Eksternal') {
                kat.value = '';        // Reset nilainya
                kat.disabled = true;   // Kunci field-nya
            } else {
                kat.disabled = false;  // Aktifkan kalau Internal / Kosong
            }

            if (!src) {
                sel.innerHTML = '<option value="">Pilih Sumber Terlebih Dahulu</option>';
                sel.disabled = true;
                return;
            }

            sel.disabled = false;

            if (src === 'Internal') {
                const internal = ambilKegiatanInternal();
                if (internal.length) {
                    const og = document.createElement('optgroup');
                    og.label = 'Internal (Kegiatan Satpol PP)';
                    internal.forEach(k => {
                        const o = document.createElement('option');
                        o.value = k.nama;
                        o.textContent = k.nama + (k.tanggal ? ' — ' + k.tanggal : '');
                        og.appendChild(o);
                    });
                    sel.appendChild(og);
                } else {
                    sel.innerHTML = '<option value="">Belum ada kegiatan internal</option>';
                }
                } else if (src === 'Eksternal') {
                    const eksternal = ambilLaporanSelesai();
                    if (eksternal.length) {
                        const og = document.createElement('optgroup');
                        og.label = 'Eksternal (Dari Laporan)';
                        eksternal.forEach(r => {
                            const o = document.createElement('option');
                            o.value = r.kode + ' - ' + (r.deskripsi || 'Laporan');
                            o.textContent = (r.deskripsi || 'Laporan') + ' (' + r.kode + ')';
                            og.appendChild(o);
                        });
                        sel.appendChild(og);
                    } else {
                        sel.innerHTML = '<option value="">Belum ada laporan selesai</option>';
                    }
                }
            }

        function sumberTerpilih() {
            return document.getElementById('select-sumber').value || 'Internal';
        }

        function getData() {
            let data = JSON.parse(localStorage.getItem(KODE) || '[]');
            if (data.length === 0) {
                data = [
                    { nama: 'Patroli rutin kawasan Pasar Wates', sumber: 'Internal', keterangan: 'Jumlah personel terbatas saat jam ramai.', dibuat: '01/09/2026' },
                    { nama: 'Penanganan laporan kerumunan di alun-alun', sumber: 'Eksternal', keterangan: 'Koordinasi dengan aparat desa membutuhkan waktu lebih.', dibuat: '02/09/2026' },
                    { nama: 'Pengamanan acara pasar malam', sumber: 'Internal', keterangan: 'Berjalan lancar tanpa kendala.', dibuat: '28/08/2026' },
                    { nama: 'Tindak lanjut laporan PKL liar', sumber: 'Eksternal', keterangan: 'Butuh sosialisasi berulang kepada pedagang.', dibuat: '27/08/2026' },
                ];
                localStorage.setItem(KODE, JSON.stringify(data));
            }
            return data;
        }

        function saveAll(data) { localStorage.setItem(KODE, JSON.stringify(data)); }

        function renderList() {
            const data = getData();

            document.getElementById('stat-total').textContent = data.length;
            document.getElementById('stat-internal').textContent = data.filter(e => e.sumber === 'Internal').length;
            document.getElementById('stat-eksternal').textContent = data.filter(e => e.sumber !== 'Internal').length;
            document.getElementById('stat-kendala').textContent = data.filter(e => e.keterangan && /kendala|keterbatasan|terbatas|butuh|memerlukan|koordinasi/i.test(e.keterangan)).length;

            const tbody = document.getElementById('evaluasi-list');
            const kosong = document.getElementById('kosong');

            if (data.length === 0) {
                tbody.innerHTML = '';
                kosong.classList.remove('hidden');
                return;
            }
            kosong.classList.add('hidden');

            tbody.innerHTML = data.map((e, i) => {
                const gi = getData().findIndex(x => x.nama === e.nama && x.sumber === e.sumber && x.dibuat === e.dibuat);
                const adaKendala = e.keterangan && /kendala|keterbatasan|terbatas|butuh|memerlukan|koordinasi/i.test(e.keterangan);
                return `
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3.5 font-medium text-slate-800">${e.nama}</td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full border ${sumberClass(e.sumber)}">${e.sumber}</span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 max-w-md">
                                <div class="flex items-start gap-2">
                                    ${adaKendala ? '<span class="material-symbols-outlined text-[18px] text-amber-500 flex-shrink-0 mt-0.5">warning</span>' : ''}
                                    <span>${e.keterangan || '<span class="text-slate-400">-</span>'}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-500">${e.dibuat || '-'}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <button type="button" onclick="bukaEdit(${gi})" class="p-1.5 text-slate-400 hover:text-sidebar-active hover:bg-sidebar-active/10 rounded-lg transition-colors" title="Edit">
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
            document.getElementById('modal-judul').textContent = 'Tambah Evaluasi';
            document.getElementById('evaluasi-form').reset();
            document.querySelector('[name=edit_index]').value = '';
            document.getElementById('select-kategori').value = ''; // Reset dropdown kategori
            loadOptions();
            document.getElementById('modal').classList.remove('hidden');
        }

        function bukaEdit(i) {
            const e = getData()[i];
            const f = document.getElementById('evaluasi-form');
            f.reset();
            document.getElementById('modal-judul').textContent = 'Ubah Evaluasi';
            document.querySelector('[name=edit_index]').value = i;
            f.sumber.value = e.sumber || 'Internal';
            f.kategori.value = e.kategori || ''; // Set kategori terpilih
            loadOptions();
            f.nama.value = e.nama;
            f.keterangan.value = e.keterangan || '';
            document.getElementById('modal').classList.remove('hidden');
        }

        function tutupModal() { document.getElementById('modal').classList.add('hidden'); }

        function simpanEvaluasi(e) {
            e.preventDefault();
            const f = document.getElementById('evaluasi-form');
            const src = document.getElementById('select-sumber');

            if (!src.value) {
                alert('Pilih sumber kegiatan terlebih dahulu.');
                src.focus();
                return;
            }

            if (src.value === 'Internal' && !f.kategori.value) {
                alert('Pilih kategori kegiatan.');
                f.kategori.focus();
                return;
            }

            if (!f.nama.value) {
                alert('Pilih nama kegiatan.');
                f.nama.focus();
                return;
            }

            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const dibuat = `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()}`;

            const data = {
                nama: f.nama.value.trim(),
                sumber: sumberTerpilih(),
                kategori: f.kategori.value, // Simpan kategori ke object
                keterangan: f.keterangan.value.trim(),
                dibuat: dibuat,
            };

            const all = getData();
            const idx = document.querySelector('[name=edit_index]').value;
            if (idx !== '') {
                data.dibuat = all[idx].dibuat;
                all[idx] = data;
                tampilToast('Edit berhasil! Data evaluasi telah diperbarui.');
            } else {
                all.unshift(data);
                tampilToast('Berhasil menambah data evaluasi kegiatan.');
            }
            saveAll(all);
            renderList();
            tutupModal();
        }

        function hapus(i) {
            const all = getData();
            const nama = all[i] ? `evaluasi "${all[i].nama}"` : 'evaluasi ini';
            window.konfirmasiHapus({
                title: 'Hapus Evaluasi',
                message: `Apakah Anda yakin ingin menghapus ${nama}? Data yang dihapus tidak dapat dipulihkan.`,
                onConfirm: () => {
                    all.splice(i, 1);
                    saveAll(all);
                    renderList();
                    tampilToast('Evaluasi berhasil dihapus.');
                }
            });
        }



        renderList();
    </script>
@endpush