@extends('layouts.admin')

@section('title', 'Acara & Kegiatan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div x-data="kegiatanManager()" x-init="init()" x-cloak>
        
        <!-- ======================= VIEW: LIST ======================= -->
        <div x-show="view === 'list'" x-transition.opacity.duration.300ms>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Acara &amp; Kegiatan</h1>
                    <p class="text-sm text-slate-500 mt-1">Kelola agenda operasional, sosialisasi, dan penertiban berkala.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Tombol Ekspor Excel -->
                    <button @click="exportExcel()" :disabled="isExportingExcel" type="button" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 rounded-xl text-sm font-semibold text-white shadow-sm transition-all flex items-center gap-2" title="Ekspor data kegiatan yang difilter ke Excel (.xlsx)">
                        <span class="material-symbols-outlined text-[18px]" :class="isExportingExcel ? 'animate-spin' : ''" x-text="isExportingExcel ? 'progress_activity' : 'table_view'"></span>
                        <span x-text="isExportingExcel ? 'Mengunduh...' : 'Ekspor Excel'"></span>
                    </button>

                    <!-- Tombol Ekspor PDF -->
                    <button @click="exportPDF()" :disabled="isExportingPdf" type="button" class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-60 rounded-xl text-sm font-semibold text-white shadow-sm transition-all flex items-center gap-2" title="Ekspor data kegiatan yang difilter ke PDF (.pdf)">
                        <span class="material-symbols-outlined text-[18px]" :class="isExportingPdf ? 'animate-spin' : ''" x-text="isExportingPdf ? 'progress_activity' : 'picture_as_pdf'"></span>
                        <span x-text="isExportingPdf ? 'Membuat PDF...' : 'Ekspor PDF'"></span>
                    </button>

                    <!-- Tombol Tambah Kegiatan -->
                    <button @click="openCreate()" type="button" class="px-4 py-2.5 bg-sidebar-active hover:bg-sidebar-hover rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Kegiatan</span>
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm mb-6 flex flex-col lg:flex-row gap-4 items-end">
                <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filter Bulan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bulan Kegiatan</label>
                        <input type="month" x-model="filterBulan" @change="onMonthChange()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm text-slate-700">
                    </div>
                    <!-- Mulai Tanggal -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                        <input type="date" x-model="filterDateStart" @change="onDateChange()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm text-slate-700">
                    </div>
                    <!-- Sampai Tanggal -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" x-model="filterDateEnd" @change="onDateChange()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm text-slate-700">
                    </div>
                    <!-- Jenis Kegiatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Jenis Kegiatan</label>
                        <select x-model="filterJenis" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm text-slate-700">
                            <option value="">Semua Jenis</option>
                            <option value="Internal">Internal</option>
                            <option value="Eksternal">Eksternal</option>
                        </select>
                    </div>
                </div>
                <div class="w-full lg:w-auto shrink-0 flex items-center gap-2">
                    <button @click="resetFilter()" class="w-full lg:w-auto px-4 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-1.5 shadow-sm bg-white">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Total Data & Filter Indicator -->
            <div class="flex items-center justify-between mb-4 px-1">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <span>Menampilkan <strong class="text-slate-800" x-text="filteredKegiatans().length"></strong> kegiatan</span>
                    <span x-show="filterBulan || filterDateStart || filterDateEnd || filterJenis" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        <span class="material-symbols-outlined text-[14px]">filter_alt</span>
                        <span x-text="getFilterInfo()"></span>
                    </span>
                </div>
            </div>

            <!-- Kegiatan List -->
            <div x-show="kegiatanList.length > 0" class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Nama Kegiatan</th>
                                <th class="px-6 py-4 font-bold">Tanggal</th>
                                <th class="px-6 py-4 font-bold">Jenis</th>
                                <th class="px-6 py-4 font-bold">Lokasi</th>
                                <th class="px-6 py-4 font-bold text-center">Peserta</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredKegiatans()" :key="item.id">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span x-text="item.nama"></span>
                                            <span x-show="item.dariLaporan" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 shrink-0">
                                                <span class="material-symbols-outlined text-[12px]">link</span>
                                                Dari Laporan
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-slate-500">
                                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                                            <span x-text="item.tanggal + ' ' + (item.waktu || '')"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border"
                                              :class="item.jenis === 'Eksternal' ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100'"
                                              x-text="item.jenis"></span>
                                    </td>
                                    <td class="px-6 py-4 truncate max-w-[200px]" x-text="item.lokasi"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-full text-xs font-semibold border border-blue-100" x-text="item.peserta.length + ' Orang'"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openDetail(item)" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Detail">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </button>
                                            <button @click="editKegiatan(item)" class="p-1.5 text-slate-400 hover:text-sidebar-active hover:bg-sidebar-active/10 rounded-lg transition-colors tooltip" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button @click="deleteKegiatan(item.id)" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors tooltip" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredKegiatans().length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    Tidak ada kegiatan yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State / Placeholder List -->
            <div x-show="kegiatanList.length === 0" class="bg-white border border-slate-200/80 rounded-2xl p-12 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">calendar_month</span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum ada kegiatan</h3>
                <p class="text-slate-500 text-sm max-w-sm">Klik "Tambah Kegiatan" untuk mulai mencatat agenda, operasi penertiban, atau sosialisasi.</p>
            </div>
        </div>

        <!-- ======================= VIEW: DETAIL ======================= -->
        <div x-show="view === 'detail'" x-transition.opacity.duration.300ms style="display: none;">
            <div x-if="selectedKegiatan">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'list'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold font-headline text-slate-900">Detail Acara &amp; Kegiatan</h2>
                        <p class="text-sm text-slate-500 mt-1" x-text="selectedKegiatan?.nama"></p>
                        <span x-show="selectedKegiatan?.dariLaporan" class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <span class="material-symbols-outlined text-[14px]">link</span>
                            Dicatat dari penyelesaian laporan <span x-text="selectedKegiatan?.kodeLaporan"></span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kiri: Info Utama & Media -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Card Informasi Dasar -->
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400">event</span>
                                    Informasi Dasar
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                                    <div class="md:col-span-2">
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama Kegiatan</div>
                                        <div class="font-bold text-slate-800 text-lg" x-text="selectedKegiatan?.nama"></div>
                                    </div>
                                    
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pelaksanaan</div>
                                        <div class="font-medium text-slate-700 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                                            <span x-text="selectedKegiatan?.tanggal"></span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Waktu Pelaksanaan</div>
                                        <div class="font-medium text-slate-700 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400">schedule</span>
                                            <span x-text="selectedKegiatan?.waktu || '-'"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Deskripsi -->
                                    <div class="md:col-span-2 mt-4 pt-4 border-t border-slate-100">
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi / Detail Acara</div>
                                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap" x-text="selectedKegiatan?.deskripsi || '-'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Dokumentasi Media -->
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">perm_media</span>
                                Dokumentasi Media
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <!-- Dummy Images -->
                                <div class="aspect-square bg-slate-100 rounded-xl border border-slate-200 overflow-hidden cursor-pointer group">
                                    <img src="https://images.unsplash.com/photo-1541872703-74c5e44368f9?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="aspect-square bg-slate-100 rounded-xl border border-slate-200 overflow-hidden cursor-pointer group">
                                    <img src="https://images.unsplash.com/photo-1577412647305-991150c7d163?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <!-- Dummy Video Placeholder -->
                                <div class="aspect-square bg-slate-800 rounded-xl border border-slate-700 overflow-hidden relative cursor-pointer group flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-4xl bg-black/50 p-3 rounded-full group-hover:scale-110 transition-transform duration-300">play_arrow</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Lokasi & Peserta -->
                    <div class="space-y-6">
                        <!-- Card Lokasi -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">location_on</span>
                                Lokasi Kejadian
                            </h3>
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3 mb-4">
                                <span class="material-symbols-outlined text-sidebar-active mt-0.5">pin_drop</span>
                                <div>
                                    <div class="font-semibold text-slate-800 text-sm leading-relaxed" x-text="selectedKegiatan?.lokasi"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-4" x-show="selectedKegiatan?.lat && selectedKegiatan?.lng">
                                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Latitude</div>
                                    <div class="text-xs font-mono text-slate-700 mt-0.5" x-text="selectedKegiatan?.lat"></div>
                                </div>
                                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Longitude</div>
                                    <div class="text-xs font-mono text-slate-700 mt-0.5" x-text="selectedKegiatan?.lng"></div>
                                </div>
                            </div>

                            <div x-show="selectedKegiatan?.lat && selectedKegiatan?.lng" class="aspect-square bg-slate-100 rounded-xl border border-slate-200 overflow-hidden relative group">
                                <div id="map-detail-kegiatan" class="w-full h-full z-10"></div>
                            </div>
                        </div>

                        <!-- Card Peserta -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">groups</span>
                                Anggota Bertugas (<span x-text="selectedKegiatan?.peserta?.length || 0"></span>)
                            </h3>
                            <div class="max-h-64 overflow-y-auto pr-2 space-y-3">
                                <template x-for="pid in (selectedKegiatan?.peserta || [])" :key="pid">
                                    <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg transition-colors border border-transparent hover:border-slate-100">
                                        <div class="w-8 h-8 bg-sidebar-active/10 text-sidebar-active rounded-full flex items-center justify-center font-bold text-xs">
                                            <span x-text="allAnggota.find(a => a.id === pid)?.nama.substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-800" x-text="allAnggota.find(a => a.id === pid)?.nama"></div>
                                            <div class="text-xs text-slate-500" x-text="allAnggota.find(a => a.id === pid)?.jabatan"></div>
                                        </div>
                                    </div>
                                </template>
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
                        <h2 class="font-headline font-bold text-lg text-slate-800" x-text="isEdit ? 'Edit Acara / Kegiatan' : 'Tambah Acara / Kegiatan'"></h2>
                        <p class="text-sm text-slate-500 mt-0.5" x-text="isEdit ? 'Ubah informasi kegiatan di bawah ini.' : 'Lengkapi form di bawah untuk menjadwalkan kegiatan baru.'"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-100">
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
                                <div class="md:col-span-2" x-show="!isEdit">
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
                                    <input type="text" x-model="formNama" placeholder="Contoh: Operasi Penertiban PKL Tanah Abang" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
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

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi / Detail Acara</label>
                                    <textarea x-model="formDeskripsi" rows="3" placeholder="Tuliskan tujuan kegiatan, sasaran, atau keterangan tambahan lainnya..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-y"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Peserta / Anggota -->
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sidebar-active">group</span>
                                Penugasan Anggota
                            </h3>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Pilih Anggota -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Anggota yang Mengikuti <span class="text-red-500">*</span></label>
                                    
                                    <!-- Search Input -->
                                    <div class="relative mb-3">
                                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
                                        <input type="text" x-model="searchAnggota" placeholder="Cari nama, jabatan, atau seksi..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                                    </div>
                                    
                                    <!-- List Anggota -->
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
                                            <div x-show="filteredAnggota.length === 0" class="text-center p-4 text-sm text-slate-500">
                                                Tidak ada anggota yang cocok.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Anggota Terpilih -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Anggota Terpilih (<span x-text="selectedAnggota.length"></span>)
                                    </label>
                                    <div class="border border-slate-200 rounded-xl overflow-hidden h-[240px] flex flex-col bg-white">
                                        <div class="flex-1 overflow-y-auto p-3">
                                            <div x-show="selectedAnggota.length === 0" class="text-center mt-10 text-sm text-slate-400">
                                                Belum ada anggota yang dipilih.
                                            </div>
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

                        <!-- Lokasi & Map -->
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

                            <!-- Peta Container (Wajib) -->
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
                                    <div id="map-kegiatan" class="w-full h-[320px] bg-slate-100 z-10"></div>
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Upload Foto (Multiple) -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Dokumentasi (Maks 5)</label>
                                    <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:bg-slate-100 transition-colors cursor-pointer mb-4 relative">
                                        <span class="material-symbols-outlined text-3xl text-slate-400 mb-2">add_a_photo</span>
                                        <p class="text-sm font-medium text-slate-700 mb-1">Pilih foto atau seret ke sini</p>
                                        <p class="text-xs text-slate-500">Mendukung banyak file sekaligus (JPG, PNG)</p>
                                        <input type="file" multiple accept="image/jpeg, image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handlePhotoUpload($event)">
                                    </label>
                                    
                                    <!-- Photo Previews -->
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

                                <!-- Upload Video (Single) -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Video Dokumentasi (Maks 1)</label>
                                    <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:bg-slate-100 transition-colors cursor-pointer mb-4 relative">
                                        <span class="material-symbols-outlined text-3xl text-slate-400 mb-2">movie</span>
                                        <p class="text-sm font-medium text-slate-700 mb-1">Unggah 1 video bukti</p>
                                        <p class="text-xs text-slate-500">Format MP4 (Maks 50MB)</p>
                                        <!-- Disable input jika sudah ada video -->
                                        <input type="file" accept="video/mp4, video/quicktime" class="absolute inset-0 w-full h-full cursor-pointer" :class="previewVideo ? 'hidden' : 'opacity-0'" @change="handleVideoUpload($event)" :disabled="previewVideo !== null">
                                    </label>

                                    <!-- Video Preview -->
                                    <div x-show="previewVideo !== null" class="relative rounded-xl border border-slate-200 overflow-hidden bg-black shadow-sm aspect-video flex items-center justify-center">
                                        <video :src="previewVideo?.url" controls class="w-full h-full max-h-[200px]"></video>
                                        <button type="button" @click="removeVideo()" class="absolute top-2 right-2 w-8 h-8 bg-white/90 hover:bg-red-50 hover:text-red-600 text-slate-700 rounded-full flex items-center justify-center shadow-md transition-colors backdrop-blur z-20">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Form Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="closeModal()" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
                        Batal
                    </button>
                        <button @click="saveKegiatan()" class="px-6 py-2.5 bg-sidebar-active hover:bg-sidebar-hover text-white rounded-xl font-semibold text-sm transition-colors shadow-md flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span x-text="isEdit ? 'Perbarui Kegiatan' : 'Simpan Kegiatan'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function kegiatanManager() {
            return {
                view: 'list', // 'list', 'detail'
                showModal: false,
                
                // Filter States
                filterBulan: '',
                filterDateStart: '',
                filterDateEnd: '',
                filterJenis: '',
                selectedKegiatan: null,
                mapDetailInstance: null,

                // Export Loading States
                isExportingExcel: false,
                isExportingPdf: false,
                
                // Form Data
                formNama: '',
                formTanggal: '',
                formWaktu: '',
                formDeskripsi: '',
                formLokasi: '',
                formLat: '',
                formLng: '',
                searchLokasi: '',
                formTemplate: '',
                
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
                
                isEdit: false,
                editId: null,

                kegiatanList: [
                    { 
                        id: 1, 
                        nama: 'Operasi Yustisi KTP', 
                        tanggal: '2023-11-15', 
                        waktu: '08:00',
                        deskripsi: 'Pemeriksaan kepemilikan dan masa berlaku KTP bagi pendatang baru.',
                        lokasi: 'Terminal Kampung Rambutan', 
                        lat: '-6.309325', 
                        lng: '106.883447',
                        peserta: [1, 3, 5],
                        jenis: 'Eksternal'
                    },
                    { 
                        id: 2, 
                        nama: 'Penertiban PKL Pasar Tanah Abang', 
                        tanggal: '2023-11-10', 
                        waktu: '10:00',
                        deskripsi: 'Penertiban pedagang kaki lima yang memakan badan jalan dan trotoar.',
                        lokasi: 'Jl. Kebon Jati, Tanah Abang', 
                        lat: '-6.187310', 
                        lng: '106.816000',
                        peserta: [2, 4, 6],
                        jenis: 'Eksternal'
                    },
                    { 
                        id: 3, 
                        nama: 'Sosialisasi Perda Kawasan Tanpa Rokok', 
                        tanggal: '2023-11-20', 
                        waktu: '13:00',
                        deskripsi: 'Memberikan pemahaman kepada warga terkait larangan merokok di fasilitas umum.',
                        lokasi: 'Taman Suropati, Menteng', 
                        lat: '-6.199732', 
                        lng: '106.832626',
                        peserta: [1, 2, 7],
                        jenis: 'Internal'
                    },
                ],
                
                // Participants
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
                
                // Map Options
                mapInstance: null,
                markerInstance: null,
                
                // Media
                previewPhotos: [],
                previewVideo: null,

                init() {
                    try {
                        const saved = JSON.parse(localStorage.getItem('sipraja_kegiatan') || 'null');
                        if (Array.isArray(saved) && saved.length) {
                            this.kegiatanList = saved;
                        } else {
                            localStorage.setItem('sipraja_kegiatan', JSON.stringify(this.kegiatanList));
                        }
                    } catch (e) {}
                    this.$watch('showModal', val => {
                        if (val) {
                            setTimeout(() => {
                                this.initMap();
                                if (this.mapInstance) {
                                    this.mapInstance.invalidateSize();
                                }
                                if (this.isEdit && this.formLat && this.formLng && this.mapInstance) {
                                    const latlng = [parseFloat(this.formLat), parseFloat(this.formLng)];
                                    this.mapInstance.setView(latlng, 16);
                                    if (this.markerInstance) {
                                        this.markerInstance.setLatLng(latlng);
                                    } else {
                                        this.markerInstance = L.marker(latlng).addTo(this.mapInstance);
                                    }
                                } else if (!this.isEdit && this.mapInstance) {
                                    this.mapInstance.setView([-6.175110, 106.827153], 13);
                                }
                            }, 150);
                            setTimeout(() => {
                                if (this.mapInstance) {
                                    this.mapInstance.invalidateSize();
                                }
                            }, 350);
                        }
                    });
                },

                get filteredAnggota() {
                    if (!this.searchAnggota) return this.allAnggota;
                    const q = this.searchAnggota.toLowerCase();
                    return this.allAnggota.filter(a => 
                        a.nama.toLowerCase().includes(q) || 
                        a.jabatan.toLowerCase().includes(q) || 
                        a.seksi.toLowerCase().includes(q)
                    );
                },

                isAnggotaSelected(id) {
                    return this.selectedAnggota.some(a => a.id === id);
                },

                toggleAnggota(anggota) {
                    const idx = this.selectedAnggota.findIndex(a => a.id === anggota.id);
                    if (idx > -1) {
                        this.selectedAnggota.splice(idx, 1);
                    } else {
                        this.selectedAnggota.push(anggota);
                    }
                },

                removeSelectedAnggota(index) {
                    this.selectedAnggota.splice(index, 1);
                },

                onMonthChange() {
                    if (this.filterBulan) {
                        this.filterDateStart = '';
                        this.filterDateEnd = '';
                    }
                },

                onDateChange() {
                    if (this.filterDateStart || this.filterDateEnd) {
                        this.filterBulan = '';
                    }
                },

                filteredKegiatans() {
                    return this.kegiatanList.filter(item => {
                        let match = true;
                        if (this.filterBulan) {
                            match = match && item.tanggal && item.tanggal.startsWith(this.filterBulan);
                        }
                        if (this.filterDateStart) {
                            match = match && (item.tanggal >= this.filterDateStart);
                        }
                        if (this.filterDateEnd) {
                            match = match && (item.tanggal <= this.filterDateEnd);
                        }
                        if (this.filterJenis) {
                            match = match && (item.jenis === this.filterJenis);
                        }
                        return match;
                    }).sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
                },

                resetFilter() {
                    this.filterBulan = '';
                    this.filterDateStart = '';
                    this.filterDateEnd = '';
                    this.filterJenis = '';
                },

                getFilterInfo() {
                    let parts = [];
                    if (this.filterBulan) {
                        const [year, month] = this.filterBulan.split('-');
                        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        parts.push(`Bulan ${monthNames[parseInt(month)] || month} ${year}`);
                    } else if (this.filterDateStart && this.filterDateEnd) {
                        parts.push(`${this.filterDateStart} s/d ${this.filterDateEnd}`);
                    } else if (this.filterDateStart) {
                        parts.push(`Mulai ${this.filterDateStart}`);
                    } else if (this.filterDateEnd) {
                        parts.push(`Sampai ${this.filterDateEnd}`);
                    } else {
                        parts.push('Semua Periode');
                    }

                    if (this.filterJenis) {
                        parts.push(`Jenis: ${this.filterJenis}`);
                    }
                    return parts.join(' • ');
                },

                getFilterFileSuffix() {
                    let suffix = '';
                    if (this.filterBulan) {
                        suffix = this.filterBulan;
                    } else if (this.filterDateStart || this.filterDateEnd) {
                        suffix = `${this.filterDateStart || 'awal'}_sd_${this.filterDateEnd || 'akhir'}`;
                    } else {
                        suffix = 'Semua_Periode';
                    }
                    if (this.filterJenis) {
                        suffix += `_${this.filterJenis}`;
                    }
                    return suffix;
                },

                loadScript(src) {
                    return new Promise((resolve, reject) => {
                        if (document.querySelector(`script[src="${src}"]`)) {
                            resolve();
                            return;
                        }
                        const script = document.createElement('script');
                        script.src = src;
                        script.onload = resolve;
                        script.onerror = () => reject(new Error('Gagal memuat pustaka dari CDN: ' + src));
                        document.head.appendChild(script);
                    });
                },

                async exportExcel() {
                    const list = this.filteredKegiatans();
                    if (!list || list.length === 0) {
                        alert('Tidak ada data kegiatan yang sesuai dengan filter untuk diekspor.');
                        return;
                    }

                    this.isExportingExcel = true;
                    try {
                        if (typeof XLSX === 'undefined') {
                            await this.loadScript('https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js');
                        }

                        const dataRows = list.map((item, index) => {
                            let namaPeserta = '-';
                            if (item.peserta && item.peserta.length > 0) {
                                const members = this.allAnggota
                                    .filter(a => item.peserta.includes(a.id))
                                    .map(a => `${a.nama} (${a.jabatan})`);
                                if (members.length > 0) {
                                    namaPeserta = members.join(', ');
                                }
                            }

                            return {
                                'No': index + 1,
                                'Nama Kegiatan': item.nama,
                                'Tanggal': item.tanggal,
                                'Waktu': item.waktu ? item.waktu + ' WIB' : '-',
                                'Jenis': item.jenis,
                                'Lokasi': item.lokasi,
                                'Koordinat (Lat, Lng)': (item.lat && item.lng) ? `${item.lat}, ${item.lng}` : '-',
                                'Jumlah Anggota': item.peserta ? item.peserta.length + ' Orang' : '0',
                                'Daftar Anggota Bertugas': namaPeserta,
                                'Deskripsi / Catatan': item.deskripsi || '-'
                            };
                        });

                        const worksheet = XLSX.utils.json_to_sheet(dataRows);
                        worksheet['!cols'] = [
                            { wch: 6 },
                            { wch: 35 },
                            { wch: 14 },
                            { wch: 12 },
                            { wch: 12 },
                            { wch: 35 },
                            { wch: 25 },
                            { wch: 16 },
                            { wch: 45 },
                            { wch: 45 },
                        ];

                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(workbook, worksheet, 'Data Kegiatan');
                        const filename = `Laporan_Kegiatan_SatpolPP_${this.getFilterFileSuffix()}.xlsx`;
                        XLSX.writeFile(workbook, filename);
                    } catch (err) {
                        alert('Terjadi kesalahan saat memproses ekspor Excel: ' + err.message);
                    } finally {
                        this.isExportingExcel = false;
                    }
                },

                async exportPDF() {
                    const list = this.filteredKegiatans();
                    if (!list || list.length === 0) {
                        alert('Tidak ada data kegiatan yang sesuai dengan filter untuk diekspor.');
                        return;
                    }

                    this.isExportingPdf = true;
                    try {
                        if (!window.jspdf || !window.jspdf.jsPDF) {
                            await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
                        }
                        if (!window.jspdf || !window.jspdf.jsPDF.prototype.autoTable) {
                            await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js');
                        }

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

                        // Header Kop Surat
                        doc.setFont('helvetica', 'bold');
                        doc.setFontSize(13);
                        doc.setTextColor(30, 41, 59);
                        doc.text('PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA', doc.internal.pageSize.getWidth() / 2, 14, { align: 'center' });
                        
                        doc.setFontSize(15);
                        doc.text('SATUAN POLISI PAMONG PRAJA (SATPOL PP)', doc.internal.pageSize.getWidth() / 2, 21, { align: 'center' });
                        
                        doc.setFont('helvetica', 'normal');
                        doc.setFontSize(10);
                        doc.setTextColor(100, 116, 139);
                        doc.text('LAPORAN REKAPITULASI AGENDA & KEGIATAN OPERASIONAL', doc.internal.pageSize.getWidth() / 2, 27, { align: 'center' });

                        // Garis Pemisah Kop
                        doc.setDrawColor(203, 213, 225);
                        doc.setLineWidth(0.6);
                        doc.line(14, 30, doc.internal.pageSize.getWidth() - 14, 30);

                        // Metadata Filter & Waktu Cetak
                        doc.setFontSize(8.5);
                        doc.setTextColor(71, 85, 105);
                        const filterText = `Kriteria Filter: ${this.getFilterInfo()}`;
                        const dateText = `Dicetak: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })} WIB`;
                        doc.text(filterText, 14, 36);
                        doc.text(dateText, doc.internal.pageSize.getWidth() - 14, 36, { align: 'right' });

                        // Tabel Data
                        const tableHeaders = [['No', 'Nama Kegiatan', 'Tanggal & Waktu', 'Jenis', 'Lokasi', 'Peserta', 'Keterangan']];
                        const tableBody = list.map((item, index) => {
                            let anggotaNames = '-';
                            if (item.peserta && item.peserta.length > 0) {
                                const members = this.allAnggota.filter(a => item.peserta.includes(a.id)).map(a => a.nama);
                                if (members.length > 0) anggotaNames = members.join(', ');
                            }
                            const datetime = item.tanggal + (item.waktu ? '\n' + item.waktu + ' WIB' : '');
                            return [
                                index + 1,
                                item.nama,
                                datetime,
                                item.jenis,
                                item.lokasi,
                                `${item.peserta ? item.peserta.length : 0} Anggota\n(${anggotaNames})`,
                                item.deskripsi || '-'
                            ];
                        });

                        doc.autoTable({
                            head: tableHeaders,
                            body: tableBody,
                            startY: 40,
                            theme: 'striped',
                            headStyles: {
                                fillColor: [170, 77, 0], // Warna Satpol Brown Admin
                                textColor: 255,
                                fontSize: 9,
                                fontStyle: 'bold',
                                halign: 'center'
                            },
                            bodyStyles: {
                                fontSize: 8,
                                textColor: [30, 41, 59],
                                cellPadding: 3
                            },
                            columnStyles: {
                                0: { halign: 'center', cellWidth: 10 },
                                1: { cellWidth: 50, fontStyle: 'bold' },
                                2: { halign: 'center', cellWidth: 28 },
                                3: { halign: 'center', cellWidth: 20 },
                                4: { cellWidth: 55 },
                                5: { cellWidth: 45 },
                                6: { cellWidth: 'auto' }
                            },
                            alternateRowStyles: {
                                fillColor: [248, 250, 252]
                            },
                            margin: { left: 14, right: 14 },
                            didDrawPage: function (data) {
                                const pageCount = doc.internal.getNumberOfPages();
                                doc.setFontSize(8);
                                doc.setTextColor(148, 163, 184);
                                doc.text(`Halaman ${data.pageNumber} dari ${pageCount}`, doc.internal.pageSize.getWidth() - 14, doc.internal.pageSize.getHeight() - 10, { align: 'right' });
                                doc.text('SIGAP Satpol PP - Dokumen Resmi Pemerintah Provinsi DKI Jakarta', 14, doc.internal.pageSize.getHeight() - 10);
                            }
                        });

                        // Blok Tanda Tangan
                        const finalY = doc.lastAutoTable.finalY || 40;
                        const pageHeight = doc.internal.pageSize.getHeight();
                        let signY = finalY + 12;

                        if (signY + 36 > pageHeight) {
                            doc.addPage();
                            signY = 20;
                        }

                        const pageWidth = doc.internal.pageSize.getWidth();
                        doc.setFontSize(9);
                        doc.setTextColor(30, 41, 59);
                        const todayStr = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        doc.text(`Jakarta, ${todayStr}`, pageWidth - 55, signY);
                        doc.text('Mengetahui,', pageWidth - 55, signY + 5);
                        doc.text('Kasubag Operasional Satpol PP', pageWidth - 55, signY + 10);
                        
                        doc.setFont('helvetica', 'bold');
                        doc.text('( Budi Santoso, S.STP )', pageWidth - 55, signY + 28);
                        doc.setFont('helvetica', 'normal');
                        doc.setFontSize(8);
                        doc.text('NIP. 19850315 200801 1 002', pageWidth - 55, signY + 32);

                        const filename = `Laporan_Kegiatan_SatpolPP_${this.getFilterFileSuffix()}.pdf`;
                        doc.save(filename);
                    } catch (err) {
                        alert('Terjadi kesalahan saat memproses ekspor PDF: ' + err.message);
                    } finally {
                        this.isExportingPdf = false;
                    }
                },

                openDetail(item) {
                    this.selectedKegiatan = item;
                    this.view = 'detail';
                    window.scrollTo(0,0);

                    if (item.lat && item.lng) {
                        setTimeout(() => {
                            if (this.mapDetailInstance) {
                                this.mapDetailInstance.remove();
                            }
                            
                            const latlng = [parseFloat(item.lat), parseFloat(item.lng)];
                            this.mapDetailInstance = L.map('map-detail-kegiatan', {
                                zoomControl: true,
                                dragging: true,
                                scrollWheelZoom: false
                            }).setView(latlng, 16);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '© OpenStreetMap'
                            }).addTo(this.mapDetailInstance);
                            
                            L.marker(latlng).addTo(this.mapDetailInstance);
                        }, 300);
                    }
                },

                openCreate() {
                    this.isEdit = false;
                    this.editId = null;
                    
                    this.formNama = '';
                    this.formTanggal = '';
                    this.formWaktu = '';
                    this.formDeskripsi = '';
                    this.formLokasi = '';
                    this.formLat = '';
                    this.formLng = '';
                    this.searchLokasi = '';
                    this.formJenis = 'Eksternal';
                    this.formTemplate = '';
                    this.searchAnggota = '';
                    this.selectedAnggota = [];
                    
                    this.previewPhotos.forEach(p => URL.revokeObjectURL(p.url));
                    this.previewPhotos = [];
                    if (this.previewVideo) URL.revokeObjectURL(this.previewVideo.url);
                    this.previewVideo = null;
                    
                    if (this.markerInstance && this.mapInstance) {
                        this.mapInstance.removeLayer(this.markerInstance);
                        this.markerInstance = null;
                    }

                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                applyTemplate() {
                    if (!this.formTemplate) return;
                    const tpl = this.templates.find(t => t.id === Number(this.formTemplate));
                    if (!tpl) return;
                    this.formNama = tpl.nama;
                    this.formDeskripsi = 'Pelaksanaan kegiatan: ' + tpl.nama + '.';
                },

                editKegiatan(item) {
                    this.isEdit = true;
                    this.editId = item.id;
                    
                    this.formNama = item.nama || '';
                    this.formTanggal = item.tanggal || '';
                    this.formWaktu = item.waktu || '';
                    this.formDeskripsi = item.deskripsi || '';
                    this.formLokasi = item.lokasi || '';
                    this.formLat = item.lat || '';
                    this.formLng = item.lng || '';
                    this.searchLokasi = '';
                    this.formJenis = item.jenis || 'Eksternal';
                    this.formTemplate = '';
                    
                    this.selectedAnggota = this.allAnggota.filter(a => item.peserta.includes(a.id));
                    
                    this.showModal = true;
                },

                deleteKegiatan(id) {
                    const item = this.kegiatanList.find(k => k.id === id);
                    const nama = item ? `kegiatan "${item.nama}"` : 'kegiatan ini';
                    window.konfirmasiHapus({
                        title: 'Hapus Kegiatan',
                        message: `Apakah Anda yakin ingin menghapus ${nama}? Data yang dihapus tidak dapat dipulihkan.`,
                        onConfirm: () => {
                            this.kegiatanList = this.kegiatanList.filter(k => k.id !== id);
                            localStorage.setItem('sipraja_kegiatan', JSON.stringify(this.kegiatanList));
                            window.tampilToast('Data kegiatan berhasil dihapus.');
                        }
                    });
                },

                toggleMap() {
                    // Triggered by checkbox, handled by $watch in init()
                },

                initMap() {
                    if (this.mapInstance) {
                        this.mapInstance.invalidateSize();
                        return;
                    }

                    const defaultCenter = [-6.175110, 106.827153];
                    this.mapInstance = L.map('map-kegiatan').setView(defaultCenter, 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapInstance);

                    this.mapInstance.on('click', (e) => {
                        this.formLat = e.latlng.lat.toFixed(6);
                        this.formLng = e.latlng.lng.toFixed(6);

                        if (this.markerInstance) {
                            this.markerInstance.setLatLng(e.latlng);
                        } else {
                            this.markerInstance = L.marker(e.latlng).addTo(this.mapInstance);
                        }
                    });
                },

                async cariLokasi() {
                    const q = (this.searchLokasi || '').trim();
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
                        this.formLat = lat.toFixed(6);
                        this.formLng = lng.toFixed(6);
                        if (!(this.formLokasi || '').trim()) {
                            this.formLokasi = data[0].display_name;
                        }
                        if (this.mapInstance) {
                            this.mapInstance.setView([lat, lng], 15, { animate: true });
                            if (this.markerInstance) {
                                this.markerInstance.setLatLng([lat, lng]);
                            } else {
                                this.markerInstance = L.marker([lat, lng]).addTo(this.mapInstance);
                            }
                        }
                    } catch (e) {
                        alert('Gagal mencari lokasi. Periksa koneksi internet.');
                    }
                },

                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        alert("Geolocation tidak didukung oleh browser Anda.");
                        return;
                    }
                    
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            this.formLat = lat.toFixed(6);
                            this.formLng = lng.toFixed(6);
                            
                            const latlng = [lat, lng];
                            
                            if (this.mapInstance) {
                                this.mapInstance.setView(latlng, 16);
                                if (this.markerInstance) {
                                    this.markerInstance.setLatLng(latlng);
                                } else {
                                    this.markerInstance = L.marker(latlng).addTo(this.mapInstance);
                                }
                            }
                        },
                        (error) => {
                            alert("Gagal mengambil lokasi.");
                        }
                    );
                },

                handlePhotoUpload(event) {
                    const files = event.target.files;
                    if (!files) return;
                    
                    if (this.previewPhotos.length + files.length > 5) {
                        alert('Maksimal 5 foto dokumentasi.');
                        event.target.value = '';
                        return;
                    }
                    
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        if (file.size > 5 * 1024 * 1024) {
                            alert(`File ${file.name} terlalu besar (Maks 5MB).`);
                            continue;
                        }
                        this.previewPhotos.push({
                            file: file,
                            url: URL.createObjectURL(file),
                            name: file.name
                        });
                    }
                    event.target.value = '';
                },

                removePhoto(index) {
                    URL.revokeObjectURL(this.previewPhotos[index].url);
                    this.previewPhotos.splice(index, 1);
                },

                handleVideoUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (file.size > 50 * 1024 * 1024) {
                        alert('Ukuran video terlalu besar. Maksimal 50MB.');
                        event.target.value = '';
                        return;
                    }

                    if (this.previewVideo) {
                        URL.revokeObjectURL(this.previewVideo.url);
                    }

                    this.previewVideo = {
                        file: file,
                        url: URL.createObjectURL(file),
                        name: file.name
                    };
                    event.target.value = '';
                },

                removeVideo() {
                    if (this.previewVideo) {
                        URL.revokeObjectURL(this.previewVideo.url);
                        this.previewVideo = null;
                    }
                },

                saveKegiatan() {
                    if (!this.formNama || !this.formLokasi || !this.formTanggal) {
                        alert('Harap lengkapi nama kegiatan, tanggal, dan alamat lokasi.');
                        return;
                    }
                    if (!this.formLat || !this.formLng) {
                        alert('Harap tentukan titik pin lokasi pada peta digital (wajib).');
                        return;
                    }
                    if (this.selectedAnggota.length === 0) {
                        alert('Harap tugaskan minimal 1 anggota.');
                        return;
                    }

                    if (this.isEdit) {
                        const index = this.kegiatanList.findIndex(k => k.id === this.editId);
                        if (index > -1) {
                            this.kegiatanList[index].nama = this.formNama;
                            this.kegiatanList[index].tanggal = this.formTanggal;
                            this.kegiatanList[index].waktu = this.formWaktu;
                            this.kegiatanList[index].deskripsi = this.formDeskripsi;
                            this.kegiatanList[index].lokasi = this.formLokasi;
                            this.kegiatanList[index].lat = this.formLat;
                            this.kegiatanList[index].lng = this.formLng;
                            this.kegiatanList[index].jenis = this.formJenis;
                            this.kegiatanList[index].peserta = this.selectedAnggota.map(a => a.id);
                        }
                        window.tampilToast('Edit berhasil! Data kegiatan telah diperbarui.');
                    } else {
                        const newId = Date.now();
                        this.kegiatanList.push({
                            id: newId,
                            nama: this.formNama,
                            tanggal: this.formTanggal,
                            waktu: this.formWaktu,
                            deskripsi: this.formDeskripsi,
                            lokasi: this.formLokasi,
                            lat: this.formLat,
                            lng: this.formLng,
                            jenis: this.formJenis,
                            peserta: this.selectedAnggota.map(a => a.id)
                        });
                        window.tampilToast('Berhasil menambah data kegiatan baru.');
                    }
                    
                    this.showModal = false;
                    localStorage.setItem('sipraja_kegiatan', JSON.stringify(this.kegiatanList));
                }
            }
        }
    </script>
@endsection
