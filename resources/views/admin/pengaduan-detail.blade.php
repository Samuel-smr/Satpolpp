@extends('layouts.admin')

@section('title', 'Detail Pengaduan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

    {{-- MODAL: Selesaikan Laporan (sama seperti form Tambah Acara/Kegiatan, ditambah Deskripsi Tindakan) --}}
    <div id="sel-root" x-data="selesaikanManager()" x-init="initSel()" @keydown.escape.window="closeSel()"
        class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeSel()"></div>
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <div>
                    <h2 class="font-headline font-bold text-lg text-slate-800">Selesaikan Laporan</h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Catat kegiatan penanganan laporan <strong x-text="kode" class="font-semibold text-emerald-600">--</strong> di bawah ini.
                    </p>
                </div>
                <button type="button" @click="closeSel()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8">
                <!-- Informasi Dasar -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sidebar-active">info</span>
                        Informasi Dasar
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sidebar-active text-[18px]">description</span>
                                    Pilih Template Kegiatan
                                </span>
                            </label>
                            <select x-model="formTemplate" @change="applyTemplate()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm appearance-none">
                                <option value="">-- Pilih dari template --</option>
                                <template x-for="tpl in templates" :key="tpl.id">
                                    <option :value="tpl.id" x-text="tpl.nama"></option>
                                </template>
                            </select>
                            <p class="text-xs text-slate-400 mt-1.5">Pilih template untuk mengisi nama kegiatan secara otomatis (opsional).</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Acara / Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formNama" placeholder="Contoh: Penanganan Laporan SPJ-584021" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                            <input type="date" x-model="formTanggal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Pelaksanaan</label>
                            <input type="time" x-model="formWaktu" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
                            <select x-model="formJenis" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                                <option value="Internal">Internal</option>
                                <option value="Eksternal">Eksternal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Status Penanganan</label>
                            <input type="text" value="Laporan Selesai" readonly class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 outline-none cursor-not-allowed">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi / Detail Acara</label>
                            <textarea x-model="formDeskripsi" rows="3" placeholder="Tuliskan tujuan penanganan, sasaran, atau keterangan tambahan lainnya..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-y"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Tindakan -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sidebar-active">task_alt</span>
                        Deskripsi Tindakan
                    </h3>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan Hasil Penanganan <span class="text-red-500">*</span></label>
                    <textarea x-model="formTindakan" rows="4" placeholder="Jelaskan tindakan yang telah dilakukan untuk menuntaskan laporan ini..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-y"></textarea>
                    <p class="text-xs text-slate-400 mt-1.5">Deskripsi ini akan ditampilkan kepada pelapor beserta foto bukti penanganan.</p>
                </div>

                <!-- Penugasan Anggota -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sidebar-active">group</span>
                        Penugasan Anggota
                    </h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Anggota yang Mengikuti <span class="text-red-500">*</span></label>
                            <div class="relative mb-3">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
                                <input type="text" x-model="searchAnggota" placeholder="Cari nama, jabatan, atau seksi..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>
                            <div class="border border-slate-200 rounded-xl overflow-hidden h-[240px] flex flex-col bg-slate-50">
                                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                                    <template x-for="anggota in filteredAnggota" :key="anggota.id">
                                        <label class="flex items-center gap-3 p-2.5 hover:bg-white rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200" :class="isAnggotaSelected(anggota.id) ? 'bg-white border-sidebar-active/30 shadow-sm' : ''">
                                            <input type="checkbox" :value="anggota.id" @change="toggleAnggota(anggota)" :checked="isAnggotaSelected(anggota.id)" class="w-4 h-4 text-sidebar-active rounded border-slate-300 focus:ring-sidebar-active">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-800" x-text="anggota.nama"></div>
                                                <div class="text-xs text-slate-500 mt-0.5">
                                                    <span x-text="anggota.jabatan"></span> &bull; <span x-text="anggota.seksi"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                    <div x-show="filteredAnggota.length === 0" class="text-center p-4 text-sm text-slate-500">Tidak ada anggota yang cocok.</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Anggota Terpilih (<span x-text="selectedAnggota.length"></span>)
                            </label>
                            <div class="border border-slate-200 rounded-xl overflow-hidden h-[240px] flex flex-col bg-white">
                                <div class="flex-1 overflow-y-auto p-3">
                                    <div x-show="selectedAnggota.length === 0" class="text-center mt-10 text-sm text-slate-400">Belum ada anggota yang dipilih.</div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(sel, idx) in selectedAnggota" :key="sel.id">
                                            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg p-2 pr-1 shadow-sm w-full">
                                                <div class="w-8 h-8 rounded bg-sidebar-active text-white flex justify-center items-center font-bold text-xs shrink-0">
                                                    <span x-text="sel.nama.substring(0,2).toUpperCase()"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800 truncate" x-text="sel.nama"></div>
                                                    <div class="text-[10px] text-slate-500 truncate" x-text="sel.jabatan"></div>
                                                </div>
                                                <button type="button" @click="removeSelectedAnggota(idx)" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors shrink-0">
                                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Kejadian -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sidebar-active">location_on</span>
                        Lokasi Kejadian
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400">pin_drop</span>
                            <input type="text" x-model="formLokasi" placeholder="Tulis alamat atau deskripsi lokasi manual secara detail..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-3 border-t border-slate-100 pt-4 mt-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Peta Digital <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-0.5">Sematkan pin di peta untuk keakuratan data koordinat lokasi (wajib).</p>
                        </div>
                    </div>
                    <div>
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <div class="flex gap-2 p-2.5 bg-white border-b border-slate-200">
                                <div class="flex-1 relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                                    <input type="text" x-model="searchLokasi" placeholder="Cari lokasi / alamat..." @keydown.enter.prevent="cariLokasi()" class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                                </div>
                                <button type="button" @click="cariLokasi()" class="inline-flex items-center justify-center gap-1 bg-sidebar-active hover:bg-sidebar-hover text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">search</span>
                                    Cari
                                </button>
                                <button type="button" @click="getCurrentLocation()" title="Gunakan lokasi saat ini" class="w-10 h-10 inline-flex items-center justify-center text-sidebar-active hover:text-white hover:bg-sidebar-active border border-sidebar-active/40 hover:border-sidebar-active rounded-lg transition-all shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">my_location</span>
                                </button>
                            </div>
                            <div id="map-selesai" class="w-full h-[320px] bg-slate-100 z-10"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Latitude <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formLat" readonly placeholder="Klik peta..." class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Longitude <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formLng" readonly placeholder="Klik peta..." class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dokumentasi -->
                <div>
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sidebar-active">perm_media</span>
                        Dokumentasi
                    </h3>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Bukti Hasil Penanganan (Maks 5) <span class="text-red-500">*</span></label>
                        <label class="relative block border-2 border-dashed border-slate-300 bg-slate-50 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:bg-slate-100 transition-colors cursor-pointer mb-4">
                            <span class="material-symbols-outlined text-3xl text-emerald-500 mb-2">photo_camera</span>
                            <p class="text-sm font-medium text-slate-700 mb-1">Pilih foto bukti penanganan</p>
                            <p class="text-xs text-slate-500">Mendukung banyak file sekaligus (JPG, PNG) - 8MB/file</p>
                            <input type="file" multiple accept="image/jpeg,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handlePhotoUpload($event)">
                        </label>
                        <div class="grid grid-cols-3 gap-3" x-show="previewPhotos.length > 0">
                            <template x-for="(photo, idx) in previewPhotos" :key="idx">
                                <div class="relative aspect-square rounded-lg border border-slate-200 overflow-hidden group bg-slate-100 shadow-sm">
                                    <img :src="photo.url" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex justify-end p-1.5">
                                        <button type="button" @click="removePhoto(idx)" class="w-6 h-6 bg-white hover:bg-red-50 hover:text-red-600 text-slate-700 rounded-full flex items-center justify-center shadow-sm transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">close</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
                <button type="button" @click="closeSel()" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
                    Batal
                </button>
                <button @click="simpan()" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold text-sm transition-colors shadow-md flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    Selesaikan &amp; Simpan
                </button>
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
            if (window.selManager) window.selManager.openSel(rec.kode);
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

    // ===== Selesaikan Laporan (form seperti Tambah Acara/Kegiatan + Deskripsi Tindakan) =====
    function selesaikanManager() {
        return {
            kode: '',
            // Form (sama seperti kegiatan)
            formTemplate: '',
            formNama: '',
            formTanggal: '',
            formWaktu: '',
            formJenis: 'Internal',
            formDeskripsi: '',
            formLokasi: '',
            formLat: '',
            formLng: '',
            searchLokasi: '',
            formTindakan: '',
            templates: [
                { id: 1, nama: 'Permintaan Data dan Informasi terkait Ketertiban Umum dan Ketentraman Masyarakat' },
                { id: 2, nama: 'Pelayanan Surat Menyurat' },
                { id: 3, nama: 'Patroli Ketentraman, Ketertiban Umum dan Perlindungan Masyarakat' },
                { id: 4, nama: 'Permohonan Bantuan Pengamanan Unjuk Rasa' },
                { id: 5, nama: 'Permohonan Bantuan Pengamanan Kegiatan' },
                { id: 6, nama: 'Penanganan Pengaduan Pelanggaran Ketertiban Umum/Masyarakat' },
                { id: 7, nama: 'Penegakan Peraturan Daerah dan Peraturan Kepala Daerah' },
                { id: 8, nama: 'Pembinaan dan Pengawasan Kepatuhan terhadap Perda dan Perkada' },
                { id: 9, nama: 'Pembinaan Perlindungan Masyarakat' },
            ],
            searchAnggota: '',
            selectedAnggota: [],
            allAnggota: [
                { id: 1, nama: 'Budi Santoso', jabatan: 'Komandan Regu', seksi: 'Ketertiban Umum' },
                { id: 2, nama: 'Siti Aminah', jabatan: 'Anggota', seksi: 'Patroli' },
                { id: 3, nama: 'Ahmad Faisal', jabatan: 'Anggota', seksi: 'Ketertiban Umum' },
                { id: 4, nama: 'Rini Yulianti', jabatan: 'Penyidik', seksi: 'Penegakan Perda' },
                { id: 5, nama: 'Doni Pratama', jabatan: 'Anggota', seksi: 'Patroli' },
                { id: 6, nama: 'Eko Wibowo', jabatan: 'Wakil Komandan', seksi: 'Penegakan Perda' },
                { id: 7, nama: 'Tia Monica', jabatan: 'Staf Administrasi', seksi: 'Sekretariat' },
            ],
            previewPhotos: [],
            mapInstance: null,
            markerInstance: null,

            initSel() {
                window.selManager = this;
            },

            get filteredAnggota() {
                if (!this.searchAnggota) return this.allAnggota;
                const q = this.searchAnggota.toLowerCase();
                return this.allAnggota.filter(a => a.nama.toLowerCase().includes(q) || a.jabatan.toLowerCase().includes(q) || a.seksi.toLowerCase().includes(q));
            },

            isAnggotaSelected(id) { return this.selectedAnggota.some(a => a.id === id); },
            toggleAnggota(a) {
                const i = this.selectedAnggota.findIndex(x => x.id === a.id);
                if (i >= 0) this.selectedAnggota.splice(i, 1);
                else this.selectedAnggota.push(a);
            },
            removeSelectedAnggota(idx) { this.selectedAnggota.splice(idx, 1); },

            applyTemplate() {
                const t = this.templates.find(x => String(x.id) === String(this.formTemplate));
                if (t) this.formNama = t.nama;
            },

            async handlePhotoUpload($event) {
                for (const file of ($event.target.files || [])) {
                    if (file.size > 8 * 1024 * 1024) { alert('File ' + file.name + ' terlalu besar. Maks 8MB.'); continue; }
                    if (!file.type.startsWith('image/')) { alert('Hanya file gambar yang diperbolehkan.'); continue; }
                    const url = await this.readFile(file);
                    this.previewPhotos.push({ name: file.name, url: url });
                }
                $event.target.value = '';
            },
            removePhoto(idx) { this.previewPhotos.splice(idx, 1); },
            readFile(file) {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = e => resolve(e.target.result);
                    reader.readAsDataURL(file);
                });
            },

            initMap() {
                if (this.mapInstance) { this.mapInstance.remove(); this.mapInstance = null; this.markerInstance = null; }
                this.mapInstance = L.map('map-selesai').setView([-7.7971, 110.1646], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(this.mapInstance);
                this.mapInstance.on('click', (e) => {
                    this.formLat = e.latlng.lat.toFixed(6);
                    this.formLng = e.latlng.lng.toFixed(6);
                    if (this.markerInstance) this.markerInstance.setLatLng(e.latlng);
                    else this.markerInstance = L.marker(e.latlng).addTo(this.mapInstance);
                });
            },
            async cariLokasi() {
                const q = (this.searchLokasi || '').trim();
                if (!q) { alert('Masukkan alamat / nama lokasi terlebih dahulu.'); return; }
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                try {
                    const res = await fetch(url, { headers: { 'Accept-Language': 'id' } });
                    const data = await res.json();
                    if (!data || data.length === 0) { alert('Lokasi tidak ditemukan. Coba alamat yang lebih spesifik.'); return; }
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    this.formLat = lat.toFixed(6);
                    this.formLng = lng.toFixed(6);
                    if (!(this.formLokasi || '').trim()) this.formLokasi = data[0].display_name;
                    if (this.mapInstance) {
                        this.mapInstance.setView([lat, lng], 15, { animate: true });
                        if (this.markerInstance) this.markerInstance.setLatLng([lat, lng]);
                        else this.markerInstance = L.marker([lat, lng]).addTo(this.mapInstance);
                    }
                } catch (e) {
                    alert('Gagal mencari lokasi. Periksa koneksi internet.');
                }
            },

            getCurrentLocation() {
                if (!navigator.geolocation) { alert('Geolocation tidak didukung browser.'); return; }
                navigator.geolocation.getCurrentPosition((pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.formLat = lat.toFixed(6);
                    this.formLng = lng.toFixed(6);
                    if (this.mapInstance) {
                        if (this.markerInstance) this.markerInstance.setLatLng([lat, lng]);
                        else this.markerInstance = L.marker([lat, lng]).addTo(this.mapInstance);
                        this.mapInstance.setView([lat, lng], 15, { animate: true });
                    }
                }, () => alert('Gagal mendapatkan lokasi. Izinkan akses lokasi pada browser.'));
            },

            openSel(kode) {
                this.kode = kode;
                this.formTemplate = '';
                this.formNama = 'Penanganan Laporan ' + kode;
                this.formTanggal = new Date().toISOString().slice(0, 10);
                this.formWaktu = new Date().toTimeString().slice(0, 5);
                this.formJenis = 'Internal';
                this.formDeskripsi = '';
                this.formLokasi = '';
                this.formLat = '';
                this.formLng = '';
                this.searchLokasi = '';
                this.formTindakan = '';
                this.searchAnggota = '';
                this.selectedAnggota = [];
                this.previewPhotos = [];
                document.getElementById('sel-root').classList.remove('hidden');
                setTimeout(() => { this.initMap(); if (this.mapInstance) this.mapInstance.invalidateSize(); }, 150);
                setTimeout(() => { if (this.mapInstance) this.mapInstance.invalidateSize(); }, 400);
            },
            closeSel() {
                document.getElementById('sel-root').classList.add('hidden');
                if (this.mapInstance) { this.mapInstance.remove(); this.mapInstance = null; this.markerInstance = null; }
            },

            simpan() {
                if (!this.formNama.trim()) { alert('Wajib mengisi nama acara / kegiatan.'); return; }
                if (!this.formTanggal) { alert('Wajib mengisi tanggal pelaksanaan.'); return; }
                if (!this.formTindakan.trim()) { alert('Wajib mengisi deskripsi tindakan (keterangan hasil penanganan).'); return; }
                if (this.selectedAnggota.length === 0) { alert('Wajib memilih minimal 1 anggota yang bertugas.'); return; }
                if (!this.formLokasi.trim()) { alert('Wajib mengisi alamat lengkap.'); return; }
                if (!this.formLat || !this.formLng) { alert('Wajib menandai titik lokasi kejadian di peta.'); return; }
                if (this.previewPhotos.length === 0) { alert('Wajib mengunggah minimal 1 foto bukti hasil penanganan.'); return; }

                const all = getReports();
                const rec = all.find(x => x.kode === CURRENT_KODE);
                if (!rec) return;
                rec.statusIdx = 5;
                rec.keteranganSelesai = this.formTindakan;
                rec.buktiSelesai = this.previewPhotos.map(p => ({ name: p.name, url: p.url }));
                rec.lokasiSelesai = { lat: this.formLat, lng: this.formLng };
                rec.penanganan = {
                    nama: this.formNama,
                    tanggal: this.formTanggal,
                    waktu: this.formWaktu,
                    jenis: this.formJenis,
                    deskripsi: this.formDeskripsi,
                    lokasi: this.formLokasi,
                    peserta: this.selectedAnggota.map(a => ({ id: a.id, nama: a.nama, jabatan: a.jabatan })),
                };
                saveAll(all);

                try {
                    const keg = JSON.parse(localStorage.getItem('sipraja_kegiatan') || '[]');
                    if (!Array.isArray(keg)) keg = [];
                    const nextId = keg.reduce((m, k) => Math.max(m, k.id || 0), 0) + 1;
                    keg.unshift({
                        id: nextId,
                        nama: this.formNama,
                        tanggal: this.formTanggal,
                        waktu: this.formWaktu,
                        deskripsi: this.formDeskripsi,
                        lokasi: this.formLokasi,
                        lat: this.formLat,
                        lng: this.formLng,
                        peserta: this.selectedAnggota.map(a => a.id),
                        jenis: this.formJenis,
                        dariLaporan: true,
                        kodeLaporan: CURRENT_KODE,
                    });
                    localStorage.setItem('sipraja_kegiatan', JSON.stringify(keg));
                } catch (e) {}

                this.closeSel();
                renderDetail();
                tampilToast('Edit berhasil! Laporan diselesaikan & dicatat sebagai kegiatan penanganan.');
            },
        };
    }




    renderDetail();
</script>
@endpush
