@extends('layouts.admin')

@section('title', 'Arsip & Dokumen')

@push('scripts')
    <!-- Alpine.js for UI state management -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div x-data="arsipManager()" x-cloak>
        
        <!-- ======================= VIEW: LIST ======================= -->
        <div x-show="view === 'list'" x-transition.opacity.duration.300ms>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Arsip</h1>
                    <p class="text-sm text-slate-500 mt-1">Kelola dan lacak dokumen institusi secara aman dan terpusat.</p>
                </div>
                <div>
                    <button @click="openCreate()" type="button" class="px-4 py-2.5 bg-sidebar-active hover:bg-sidebar-hover rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">upload_file</span>
                        <span>Unggah Arsip</span>
                    </button>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex gap-2">
                    <button @click="filterTab = 'semua'" :class="filterTab === 'semua' ? 'bg-sidebar-active text-white shadow-sm' : 'bg-transparent text-slate-500 hover:bg-slate-100'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Semua Arsip</button>
                    <button @click="filterTab = 'dokumen'" :class="filterTab === 'dokumen' ? 'bg-sidebar-active text-white shadow-sm' : 'bg-transparent text-slate-500 hover:bg-slate-100'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Dokumen</button>
                    <button @click="filterTab = 'media'" :class="filterTab === 'media' ? 'bg-sidebar-active text-white shadow-sm' : 'bg-transparent text-slate-500 hover:bg-slate-100'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Media (Foto/Video)</button>
                </div>
                
                <div class="relative w-full md:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400">search</span>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama arsip..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                </div>
            </div>

            <!-- Grid Arsip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <template x-for="item in filteredArsip()" :key="item.id">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all group flex flex-col h-full relative cursor-pointer" @click.self="openDetail(item)">
                        <!-- Action Dropdown Trigger -->
                        <div class="absolute top-4 right-4 z-10" @click.stop>
                            <div x-data="{ openMenu: false }" class="relative">
                                <button @click="openMenu = !openMenu" @click.away="openMenu = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="openMenu" x-transition class="absolute right-0 top-10 w-36 bg-white border border-slate-200 shadow-lg rounded-xl overflow-hidden py-1 z-20">
                                    <button @click="openDetail(item); openMenu = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-sidebar-active flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Detail
                                    </button>
                                    <button @click="editArsip(item); openMenu = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-sidebar-active flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                                    </button>
                                    <button @click="deleteArsip(item.id); openMenu = false" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 shrink-0 cursor-pointer" :class="getIconColor(item.type)" @click="openDetail(item)">
                            <span class="material-symbols-outlined text-[24px]" x-text="getIcon(item.type)"></span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 flex flex-col" @click="openDetail(item)">
                            <h3 class="text-sm font-bold text-slate-800 leading-tight mb-1 group-hover:text-sidebar-active transition-colors line-clamp-2" x-text="item.nama"></h3>
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-3" x-text="item.kategori"></div>
                            
                            <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100">
                                <div class="text-xs text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                    <span x-text="item.tanggal"></span>
                                </div>
                                <div class="text-xs font-medium text-slate-500 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100" x-text="item.size || (item.count + ' Files')"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="filteredArsip().length === 0" class="col-span-full bg-white border border-slate-200/80 rounded-2xl p-12 shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl">folder_off</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Arsip Tidak Ditemukan</h3>
                    <p class="text-slate-500 text-sm max-w-sm">Belum ada arsip yang sesuai dengan pencarian Anda, atau belum ada file yang diunggah.</p>
                </div>
            </div>
        </div>

        <!-- ======================= VIEW: DETAIL ======================= -->
        <div x-show="view === 'detail'" x-transition.opacity.duration.300ms style="display: none;">
            <div x-if="selectedArsip">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'list'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold font-headline text-slate-900">Detail Arsip</h2>
                        <p class="text-sm text-slate-500 mt-1" x-text="selectedArsip?.nama"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kiri: Preview & Info Utama -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Preview Box -->
                        <div class="bg-slate-100 border border-slate-200 rounded-2xl h-80 flex flex-col items-center justify-center text-slate-400 relative overflow-hidden group shadow-inner">
                            <span class="material-symbols-outlined text-[80px] mb-4" :class="getTextColor(selectedArsip?.type)" x-text="getIcon(selectedArsip?.type)"></span>
                            <div class="text-sm font-semibold" x-text="'Preview ' + selectedArsip?.type.toUpperCase()"></div>
                            <button class="absolute bottom-6 px-6 py-2 bg-white/90 backdrop-blur text-sidebar-active border border-slate-200 rounded-xl font-bold text-sm shadow-sm opacity-0 group-hover:opacity-100 transition-all flex items-center gap-2 hover:bg-white hover:text-sidebar-hover">
                                <span class="material-symbols-outlined text-[18px]">download</span> Unduh File
                            </button>
                        </div>

                        <!-- Card Detail -->
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">description</span>
                                Keterangan Arsip
                            </h3>
                            
                            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap mb-6" x-text="selectedArsip?.deskripsi || 'Tidak ada deskripsi yang ditambahkan untuk arsip ini.'"></p>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</div>
                                    <div class="text-sm font-semibold text-slate-800" x-text="selectedArsip?.kategori"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ukuran</div>
                                    <div class="text-sm font-semibold text-slate-800" x-text="selectedArsip?.size || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe File</div>
                                    <div class="text-sm font-semibold text-slate-800 uppercase" x-text="selectedArsip?.type"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sifat Dokumen</div>
                                    <div class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-bold uppercase inline-block mt-0.5">Confidential</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Metadata & Riwayat -->
                    <div class="space-y-6">
                        <!-- Card Metadata -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">info</span>
                                Metadata
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="text-xs font-semibold text-slate-500 mb-1">Diunggah Oleh</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-sidebar-active/10 text-sidebar-active rounded-full flex items-center justify-center font-bold text-[10px]" x-text="selectedArsip?.uploader.substring(0,2).toUpperCase()"></div>
                                        <span class="text-sm font-semibold text-slate-800" x-text="selectedArsip?.uploader"></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-xs font-semibold text-slate-500 mb-1">Tanggal Unggah</div>
                                    <div class="text-sm font-medium text-slate-700 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                                        <span x-text="selectedArsip?.tanggal"></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs font-semibold text-slate-500 mb-1">Terakhir Diubah</div>
                                    <div class="text-sm font-medium text-slate-700 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-slate-400">update</span>
                                        <span>Hari ini, 09:15 WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Riwayat / Timeline -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">history</span>
                                Riwayat Perubahan
                            </h3>
                            
                            <div class="relative pl-3 space-y-4 border-l-2 border-slate-100">
                                <div class="relative">
                                    <div class="absolute -left-[17px] top-1 w-3 h-3 bg-amber-400 rounded-full ring-4 ring-white"></div>
                                    <div class="text-xs font-bold text-slate-800 mb-0.5">Metadata diperbarui</div>
                                    <div class="text-[10px] text-slate-500">Oleh Admin System &bull; Hari ini, 09:15</div>
                                </div>
                                <div class="relative">
                                    <div class="absolute -left-[17px] top-1 w-3 h-3 bg-slate-300 rounded-full ring-4 ring-white"></div>
                                    <div class="text-xs font-bold text-slate-800 mb-0.5">File diunggah</div>
                                    <div class="text-[10px] text-slate-500"><span x-text="'Oleh ' + selectedArsip?.uploader"></span> &bull; <span x-text="selectedArsip?.tanggal"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= MODAL: CREATE / EDIT ======================= -->
        <div x-show="showModal" 
             @keydown.escape.window="closeModal()" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                 
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                    <div>
                        <h2 class="font-headline font-bold text-lg text-slate-800" x-text="isEdit ? 'Edit Arsip / Dokumen' : 'Unggah Arsip Baru'"></h2>
                        <p class="text-sm text-slate-500 mt-0.5" x-text="isEdit ? 'Ubah informasi arsip di bawah ini.' : 'Lengkapi form untuk mengunggah dokumen arsip ke dalam sistem.'"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-100">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Arsip -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Arsip / Dokumen <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formNama" placeholder="Contoh: BAP Penertiban PKL Tanah Abang" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select x-model="formKategori" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm appearance-none">
                                <option value="" disabled selected>Pilih Kategori...</option>
                                <option value="Penertiban">Penertiban</option>
                                <option value="Pengaduan">Pengaduan</option>
                                <option value="SOP">SOP &amp; Regulasi</option>
                                <option value="Dokumen">Dokumen Umum</option>
                                <option value="Media">Dokumentasi Media</option>
                            </select>
                        </div>

                        <!-- Sifat -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Sifat Dokumen</label>
                            <select class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm appearance-none">
                                <option value="public">Biasa / Publik</option>
                                <option value="confidential">Rahasia / Confidential</option>
                            </select>
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan / Deskripsi Ringkas</label>
                            <textarea x-model="formDeskripsi" rows="3" placeholder="Tuliskan keterangan mengenai isi dokumen ini..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-y"></textarea>
                        </div>

                        <!-- Dropzone File -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Unggah File <span class="text-red-500" x-show="!isEdit">*</span></label>
                            <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:bg-slate-100 transition-colors cursor-pointer relative" :class="formFile ? 'border-sidebar-active/50 bg-sidebar-active/5' : ''">
                                <span class="material-symbols-outlined text-4xl mb-2" :class="formFile ? 'text-sidebar-active' : 'text-slate-400'">cloud_upload</span>
                                <p class="text-sm font-medium mb-1" :class="formFile ? 'text-sidebar-active' : 'text-slate-700'" x-text="formFile ? formFile.name : 'Drag and drop file arsip di sini'"></p>
                                <p class="text-xs text-slate-500" x-text="formFile ? (formFile.size / 1024 / 1024).toFixed(2) + ' MB' : 'Format yang didukung: PDF, JPG, PNG, DOCX, ZIP. Maks: 50MB.'"></p>
                                <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleFileUpload($event)">
                            </label>
                            <p class="text-xs text-amber-600 mt-2 flex items-center gap-1" x-show="isEdit">
                                <span class="material-symbols-outlined text-[14px]">info</span> Abaikan bagian file jika tidak ingin mengubah file yang sudah ada.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="closeModal()" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
                        Batal
                    </button>
                    <button @click="saveArsip()" class="px-6 py-2.5 bg-sidebar-active hover:bg-sidebar-hover text-white rounded-xl font-semibold text-sm transition-colors shadow-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span x-text="isEdit ? 'Perbarui Arsip' : 'Simpan & Unggah'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        function arsipManager() {
            return {
                view: 'list', // 'list', 'detail'
                showModal: false,
                
                isEdit: false,
                editId: null,
                filterTab: 'semua',
                searchQuery: '',

                selectedArsip: null,
                
                formNama: '',
                formKategori: '',
                formDeskripsi: '',
                formFile: null,
                
                arsipList: [
                    { 
                        id: 1, 
                        nama: 'BAP_Pasar_Senen.pdf', 
                        kategori: 'Penertiban', 
                        type: 'pdf', 
                        size: '2.4 MB', 
                        tanggal: '2023-11-15', 
                        uploader: 'Budi Santoso', 
                        deskripsi: 'Berita Acara Pemeriksaan untuk operasi penertiban PKL di sekitar Pasar Senen.' 
                    },
                    { 
                        id: 2, 
                        nama: 'SOP_Penertiban_PKL_2023.pdf', 
                        kategori: 'SOP', 
                        type: 'pdf', 
                        size: '1.1 MB', 
                        tanggal: '2023-10-10', 
                        uploader: 'Admin System', 
                        deskripsi: 'Standard Operating Procedure terbaru terkait teknis penertiban PKL di fasilitas umum.' 
                    },
                    { 
                        id: 3, 
                        nama: 'SK Penertiban 2024', 
                        kategori: 'Dokumen', 
                        type: 'folder', 
                        count: 12, 
                        tanggal: '2023-11-01', 
                        uploader: 'Kepala Satpol PP', 
                        deskripsi: 'Kumpulan draft dan Surat Keputusan Penertiban untuk periode tahun 2024.' 
                    },
                    { 
                        id: 4, 
                        nama: 'Dokumentasi_Kawasan_Monas.zip', 
                        kategori: 'Media', 
                        type: 'zip', 
                        size: '45 MB', 
                        tanggal: '2023-11-05', 
                        uploader: 'Ahmad Dahlan', 
                        deskripsi: 'Kumpulan foto dan video sebelum dan sesudah penertiban kawasan taman Monas.' 
                    },
                    { 
                        id: 5, 
                        nama: 'Laporan_Aduan_Warga_Q3.docx', 
                        kategori: 'Pengaduan', 
                        type: 'word', 
                        size: '5.2 MB', 
                        tanggal: '2023-10-20', 
                        uploader: 'Siti Aminah', 
                        deskripsi: 'Rekapitulasi aduan masyarakat dari aplikasi dan media sosial selama Kuartal 3 2023.' 
                    },
                    { 
                        id: 6, 
                        nama: 'Denah_Operasi_Yustisi_Jaktim.jpg', 
                        kategori: 'Media', 
                        type: 'image', 
                        size: '3.8 MB', 
                        tanggal: '2023-11-12', 
                        uploader: 'Budi Santoso', 
                        deskripsi: 'Peta denah pembagian zona untuk pelaksanaan Operasi Yustisi di wilayah Jakarta Timur.' 
                    },
                ],

                getIcon(type) {
                    const icons = {
                        'pdf': 'picture_as_pdf',
                        'word': 'description',
                        'folder': 'folder',
                        'zip': 'folder_zip',
                        'image': 'image'
                    };
                    return icons[type] || 'draft';
                },

                getIconColor(type) {
                    const colors = {
                        'pdf': 'bg-red-50 text-red-500',
                        'word': 'bg-blue-50 text-blue-500',
                        'folder': 'bg-amber-50 text-amber-500',
                        'zip': 'bg-purple-50 text-purple-500',
                        'image': 'bg-emerald-50 text-emerald-500'
                    };
                    return colors[type] || 'bg-slate-100 text-slate-500';
                },

                getTextColor(type) {
                    const colors = {
                        'pdf': 'text-red-300',
                        'word': 'text-blue-300',
                        'folder': 'text-amber-300',
                        'zip': 'text-purple-300',
                        'image': 'text-emerald-300'
                    };
                    return colors[type] || 'text-slate-300';
                },

                filteredArsip() {
                    return this.arsipList.filter(item => {
                        let matchTab = true;
                        if (this.filterTab === 'dokumen') {
                            matchTab = ['pdf', 'word', 'folder'].includes(item.type);
                        } else if (this.filterTab === 'media') {
                            matchTab = ['image', 'zip'].includes(item.type);
                        }

                        let matchSearch = true;
                        if (this.searchQuery) {
                            matchSearch = item.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                          item.kategori.toLowerCase().includes(this.searchQuery.toLowerCase());
                        }

                        return matchTab && matchSearch;
                    }).sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
                },

                openDetail(item) {
                    this.selectedArsip = item;
                    this.view = 'detail';
                    window.scrollTo(0,0);
                },

                openCreate() {
                    this.isEdit = false;
                    this.editId = null;
                    this.formNama = '';
                    this.formKategori = '';
                    this.formDeskripsi = '';
                    this.formFile = null;
                    
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                editArsip(item) {
                    this.isEdit = true;
                    this.editId = item.id;
                    
                    this.formNama = item.nama;
                    this.formKategori = item.kategori;
                    this.formDeskripsi = item.deskripsi;
                    this.formFile = null; // Don't preload file for edit usually
                    
                    this.showModal = true;
                },

                deleteArsip(id) {
                    const item = this.arsipList.find(a => a.id === id);
                    const nama = item ? `arsip "${item.nama || item.judul}"` : 'arsip ini';
                    window.konfirmasiHapus({
                        title: 'Hapus Arsip',
                        message: `Apakah Anda yakin ingin menghapus ${nama}? Data yang dihapus tidak dapat dipulihkan.`,
                        onConfirm: () => {
                            this.arsipList = this.arsipList.filter(a => a.id !== id);
                            if (this.view === 'detail') {
                                this.view = 'list';
                            }
                            window.tampilToast('Data arsip berhasil dihapus.');
                        }
                    });
                },

                handleFileUpload(e) {
                    if(e.target.files.length > 0) {
                        this.formFile = e.target.files[0];
                    }
                },

                saveArsip() {
                    if (!this.formNama || !this.formKategori) {
                        alert('Silakan lengkapi form (Nama Arsip dan Kategori).');
                        return;
                    }

                    if (this.isEdit) {
                        const index = this.arsipList.findIndex(a => a.id === this.editId);
                        if (index > -1) {
                            this.arsipList[index].nama = this.formNama;
                            this.arsipList[index].kategori = this.formKategori;
                            this.arsipList[index].deskripsi = this.formDeskripsi;
                            // Update type/size if new file uploaded, normally handled in backend
                            if (this.formFile) {
                                this.arsipList[index].size = (this.formFile.size / 1024 / 1024).toFixed(2) + ' MB';
                            }
                        }
                        this.showModal = false;
                        window.tampilToast('Edit berhasil! Data arsip telah diperbarui.');
                    } else {
                        if (!this.formFile) {
                            alert('Silakan unggah file terlebih dahulu.');
                            return;
                        }
                        
                        // Infer type from file extension
                        let ext = this.formFile.name.split('.').pop().toLowerCase();
                        let type = 'pdf';
                        if (['png', 'jpg', 'jpeg'].includes(ext)) type = 'image';
                        if (['zip', 'rar'].includes(ext)) type = 'zip';
                        if (['doc', 'docx'].includes(ext)) type = 'word';

                        this.arsipList.unshift({
                            id: Date.now(),
                            nama: this.formNama,
                            kategori: this.formKategori,
                            type: type,
                            size: (this.formFile.size / 1024 / 1024).toFixed(2) + ' MB',
                            tanggal: new Date().toISOString().split('T')[0],
                            uploader: 'Admin System',
                            deskripsi: this.formDeskripsi
                        });
                        this.showModal = false;
                        window.tampilToast('Berhasil menambah data arsip baru.');
                    }
                }
            }
        }
    </script>
@endsection
