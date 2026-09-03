@extends('layouts.admin')

@section('title', 'Acara & Kegiatan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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
                <div>
                    <button @click="openCreate()" type="button" class="px-4 py-2.5 bg-sidebar-active hover:bg-sidebar-hover rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Kegiatan</span>
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                        <input type="date" x-model="filterDateStart" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" x-model="filterDateEnd" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Jenis Kegiatan</label>
                        <select x-model="filterJenis" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            <option value="">Semua</option>
                            <option value="Internal">Internal</option>
                            <option value="Eksternal">Eksternal</option>
                        </select>
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <button @click="resetFilter()" class="w-full md:w-auto px-4 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2 shadow-sm bg-white">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                        Reset
                    </button>
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
                                    <td class="px-6 py-4 font-semibold text-slate-800" x-text="item.nama"></td>
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

        <!-- ======================= VIEW: CREATE ======================= -->
        <div x-show="view === 'create'" x-transition.opacity.duration.300ms style="display: none;">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'list'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold font-headline text-slate-900" x-text="isEdit ? 'Edit Acara / Kegiatan' : 'Tambah Acara / Kegiatan'"></h2>
                        <p class="text-sm text-slate-500 mt-1" x-text="isEdit ? 'Ubah informasi kegiatan di bawah ini.' : 'Lengkapi form di bawah untuk menjadwalkan kegiatan baru.'"></p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
                    <div class="p-6 md:p-8 space-y-8">
                        
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
                                    <label class="block text-sm font-semibold text-slate-700">Peta Digital (Opsional)</label>
                                    <p class="text-xs text-slate-500 mt-0.5">Sematkan pin di peta untuk keakuratan data koordinat.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="showMap" class="sr-only peer" @change="toggleMap()">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sidebar-active"></div>
                                </label>
                            </div>

                            <!-- Peta Container (Bisa disembunyikan jika dimatikan) -->
                            <div x-show="showMap" style="display: none;">
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Latitude</label>
                                        <input type="text" x-model="formLat" readonly placeholder="Otomatis dari map" class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Longitude</label>
                                        <input type="text" x-model="formLng" readonly placeholder="Otomatis dari map" class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mb-2">
                                    <button type="button" @click="getCurrentLocation()" class="text-xs font-medium text-sidebar-active hover:text-sidebar-hover flex items-center gap-1 bg-sidebar-active/10 px-2.5 py-1.5 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">my_location</span>
                                        Gunakan lokasi saat ini
                                    </button>
                                </div>
                                
                                <div class="border border-slate-200 rounded-xl overflow-hidden relative group">
                                    <div id="map-kegiatan" class="w-full h-[400px] bg-slate-100 z-10"></div>
                                    <div class="absolute top-3 right-3 z-[1000]">
                                        <span class="bg-white/90 backdrop-blur text-xs font-medium px-3 py-1.5 rounded-lg shadow-sm border border-slate-200 text-slate-600 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">touch_app</span>
                                            Klik peta untuk pin lokasi
                                        </span>
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
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 z-50">
                        <button @click="view = 'list'" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
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
                view: 'list', // 'list', 'detail', 'create'
                
                // Filter States
                filterDateStart: '',
                filterDateEnd: '',
                filterJenis: '',
                selectedKegiatan: null,
                mapDetailInstance: null,
                
                // Form Data
                formNama: '',
                formTanggal: '',
                formWaktu: '',
                formDeskripsi: '',
                formLokasi: '',
                formLat: '',
                formLng: '',
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
                showMap: false,
                mapInstance: null,
                markerInstance: null,
                
                // Media
                previewPhotos: [],
                previewVideo: null,

                init() {
                    // Watch for map toggle to initialize appropriately
                    this.$watch('showMap', val => {
                        if (val) {
                            setTimeout(() => {
                                this.initMap();
                            }, 100);
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

                filteredKegiatans() {
                    return this.kegiatanList.filter(item => {
                        let match = true;
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
                    this.filterDateStart = '';
                    this.filterDateEnd = '';
                    this.filterJenis = '';
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
                    this.formTemplate = '';
                    this.searchAnggota = '';
                    this.selectedAnggota = [];
                    this.showMap = false;
                    
                    this.previewPhotos.forEach(p => URL.revokeObjectURL(p.url));
                    this.previewPhotos = [];
                    if (this.previewVideo) URL.revokeObjectURL(this.previewVideo.url);
                    this.previewVideo = null;
                    
                    if (this.markerInstance && this.mapInstance) {
                        this.mapInstance.removeLayer(this.markerInstance);
                        this.markerInstance = null;
                    }

                    this.view = 'create';
                    window.scrollTo(0,0);
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
                    this.formJenis = item.jenis || 'Eksternal';
                    
                    this.selectedAnggota = this.allAnggota.filter(a => item.peserta.includes(a.id));
                    
                    this.showMap = !!(this.formLat && this.formLng);
                    
                    this.view = 'create';
                    window.scrollTo(0,0);
                },

                deleteKegiatan(id) {
                    if (confirm('Yakin ingin menghapus kegiatan ini?')) {
                        this.kegiatanList = this.kegiatanList.filter(k => k.id !== id);
                    }
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
                        alert('Harap lengkapi nama kegiatan, tanggal, dan lokasi manual.');
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
                        alert('Kegiatan berhasil diperbarui (Prototype).');
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
                        alert('Kegiatan berhasil dijadwalkan (Prototype).');
                    }
                    
                    this.view = 'list';
                }
            }
        }
    </script>
@endsection
