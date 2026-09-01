@extends('layouts.admin')

@section('title', 'Pengaduan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <!-- Alpine.js for UI state management -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div x-data="pengaduanManager()" x-init="init()" x-cloak>
        
        <!-- ======================= VIEW: LIST ======================= -->
        <div x-show="view === 'list'" x-transition.opacity.duration.300ms>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen Pengaduan</h1>
                    <p class="text-sm text-slate-500 mt-1">Kelola dan pantau semua laporan masyarakat.</p>
                </div>
                <div>
                    <button @click="openCreate()" type="button" class="px-4 py-2.5 bg-sidebar-active hover:bg-sidebar-hover rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Buat Laporan Baru</span>
                    </button>
                </div>
            </div>

            <!-- Tabs & Filters -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b border-slate-200 pb-4">
                <div class="flex gap-6">
                    <button @click="filterTab = 'semua'" :class="filterTab === 'semua' ? 'text-sidebar-active border-b-2 border-sidebar-active font-semibold pb-4 -mb-[17px]' : 'text-slate-500 hover:text-slate-700 font-medium pb-4 -mb-[17px]'">
                        Semua
                    </button>
                    <button @click="filterTab = 'urgent'" :class="filterTab === 'urgent' ? 'text-sidebar-active border-b-2 border-sidebar-active font-semibold pb-4 -mb-[17px]' : 'text-slate-500 hover:text-slate-700 font-medium pb-4 -mb-[17px]'" class="flex items-center gap-2">
                        Urgent <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full" x-text="countUrgent()"></span>
                    </button>
                    <button @click="filterTab = 'tidak_urgent'" :class="filterTab === 'tidak_urgent' ? 'text-sidebar-active border-b-2 border-sidebar-active font-semibold pb-4 -mb-[17px]' : 'text-slate-500 hover:text-slate-700 font-medium pb-4 -mb-[17px]'" class="flex items-center gap-2">
                        Tidak Urgent <span class="bg-blue-100 text-blue-600 text-xs px-2 py-0.5 rounded-full" x-text="countTidakUrgent()"></span>
                    </button>
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Date Range Filter -->
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 shadow-sm">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">calendar_month</span>
                        <div class="flex items-center gap-2">
                            <input type="date" x-model="filterDateStart" class="outline-none bg-transparent w-[115px]" title="Tanggal Mulai">
                            <span class="text-slate-400 text-xs">s/d</span>
                            <input type="date" x-model="filterDateEnd" class="outline-none bg-transparent w-[115px]" title="Tanggal Akhir">
                        </div>
                        <button x-show="filterDateStart || filterDateEnd" @click="filterDateStart = ''; filterDateEnd = ''" class="ml-1 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors p-0.5 rounded-full hover:bg-red-50" title="Reset Rentang Tanggal">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Kode Tiket</th>
                                <th class="px-6 py-4">Pelapor</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Lokasi</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Prioritas</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredReports()" :key="item.id">
                                <tr class="hover:bg-slate-50 transition-colors" :class="!item.is_read ? 'bg-orange-50/30' : ''">
                                    <td class="px-6 py-4 font-medium text-slate-800 flex items-center gap-2">
                                        <!-- Unread Indicator -->
                                        <div x-show="!item.is_read" class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse" title="Laporan Baru Belum Dilihat"></div>
                                        <span x-text="item.id"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div x-text="item.pelapor" class="font-medium text-slate-700"></div>
                                        <div class="text-xs text-slate-400 mt-0.5" x-text="item.sumber === 'medsos' ? 'Media Sosial' : 'Langsung (Offline)'"></div>
                                    </td>
                                    <td class="px-6 py-4" x-text="item.kategori"></td>
                                    <td class="px-6 py-4" x-text="item.lokasi"></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-red-50 text-red-600': item.status === 'Baru',
                                                  'bg-orange-100 text-orange-700': item.status === 'Diproses',
                                                  'bg-green-100 text-green-700': item.status === 'Selesai'
                                              }" x-text="item.status"></span>
                                    </td>
                                    <td class="px-6 py-4 font-medium" 
                                        :class="item.prioritas === 'High' ? 'text-red-600' : 'text-slate-500'">
                                        <span x-show="item.prioritas === 'High'" class="font-bold mr-1">!</span>
                                        <span x-text="item.prioritas"></span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openDetail(item)" class="p-1.5 text-slate-400 hover:text-sidebar-active hover:bg-sidebar-active/10 rounded-lg transition-colors tooltip" title="Detail">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </button>
                                            <button @click="editPengaduan(item)" class="p-1.5 text-slate-400 hover:text-sidebar-active hover:bg-sidebar-active/10 rounded-lg transition-colors tooltip" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button @click="deletePengaduan(item.id)" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors tooltip" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredReports().length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                    Tidak ada pengaduan yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
                    <span class="text-sm text-slate-500">Menampilkan <span x-text="filteredReports().length"></span> laporan</span>
                    <div class="flex gap-1">
                        <button class="px-3 py-1 border border-slate-200 bg-white rounded text-sm text-slate-400 cursor-not-allowed">Prev</button>
                        <button class="px-3 py-1 border border-sidebar-active bg-sidebar-active text-white rounded text-sm">1</button>
                        <button class="px-3 py-1 border border-slate-200 bg-white rounded text-sm text-slate-600 hover:bg-slate-100">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= VIEW: DETAIL ======================= -->
        <div x-show="view === 'detail'" x-transition.opacity.duration.300ms style="display: none;">
            <div x-if="selectedReport">
                <!-- Detail Header -->
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'list'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold font-headline text-slate-900">Detail Pengaduan</h2>
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold border border-slate-200" x-text="selectedReport?.status"></span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">
                            ID: <span x-text="selectedReport?.id"></span> &bull; Dilaporkan pada <span x-text="selectedReport?.tanggal"></span>
                        </p>
                    </div>
                </div>

                <!-- Main Layout for Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kiri: Info Utama -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Card Detail Laporan & Pelapor -->
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-6">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400">info</span>
                                    Informasi Lengkap Pengaduan
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                                    <!-- Pelapor -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama Pelapor</div>
                                        <div class="font-semibold text-slate-800 flex items-center gap-2">
                                            <div class="w-6 h-6 bg-sidebar-active/10 text-sidebar-active rounded-full flex items-center justify-center font-bold text-[10px]" x-text="selectedReport?.pelapor.substring(0,2).toUpperCase()"></div>
                                            <span x-text="selectedReport?.pelapor"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- No HP -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor HP / Telepon</div>
                                        <div class="font-medium text-slate-700" x-text="selectedReport?.no_hp || '-'"></div>
                                    </div>

                                    <!-- NIK -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">NIK Pelapor</div>
                                        <div class="font-medium text-slate-700" x-text="selectedReport?.nik || '-'"></div>
                                    </div>

                                    <!-- Kategori -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Kategori Pelanggaran</div>
                                        <div class="font-medium text-slate-700">
                                            <span class="bg-slate-100 px-2 py-0.5 rounded text-sm border border-slate-200" x-text="selectedReport?.kategori"></span>
                                        </div>
                                    </div>

                                    <!-- Sumber -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Sumber Laporan</div>
                                        <div class="font-medium text-slate-700 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400" x-text="selectedReport?.sumber === 'medsos' ? 'public' : 'record_voice_over'"></span>
                                            <span x-text="selectedReport?.sumber === 'medsos' ? 'Media Sosial' : 'Langsung (Offline)'"></span>
                                            <span x-show="selectedReport?.sumber === 'medsos'" class="text-slate-400 mx-1">-</span>
                                            <span x-show="selectedReport?.sumber === 'medsos'" class="capitalize font-semibold text-sidebar-active" x-text="'(' + (selectedReport?.platform || 'Lainnya') + ')'"></span>
                                        </div>
                                    </div>

                                    <!-- Tanggal & Waktu -->
                                    <div>
                                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Waktu Pelaporan</div>
                                        <div class="font-medium text-slate-700 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px] text-slate-400">schedule</span>
                                            <span x-text="selectedReport?.tanggal"></span> <span class="text-slate-400 mx-1">&bull;</span> <span x-text="selectedReport?.jam || '14:30 WIB'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-slate-50">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Aduan</div>
                                <p class="text-slate-700 leading-relaxed text-sm whitespace-pre-wrap" x-text="selectedReport?.deskripsi"></p>
                            </div>
                        </div>

                        <!-- Card Foto Bukti -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">photo_library</span>
                                Foto Bukti
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="aspect-video bg-slate-100 rounded-xl border border-slate-200 overflow-hidden group relative cursor-pointer">
                                    <img src="https://images.unsplash.com/photo-1517524965825-9f5e13b4c730?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="aspect-video bg-slate-100 rounded-xl border border-slate-200 overflow-hidden group relative cursor-pointer">
                                    <img src="https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Kanan: Lokasi & Aksi -->
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
                                    <div class="font-semibold text-slate-800 text-sm leading-relaxed" x-text="selectedReport?.lokasi"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-4" x-show="selectedReport?.lat && selectedReport?.lng">
                                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Latitude</div>
                                    <div class="text-xs font-mono text-slate-700 mt-0.5" x-text="selectedReport?.lat"></div>
                                </div>
                                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Longitude</div>
                                    <div class="text-xs font-mono text-slate-700 mt-0.5" x-text="selectedReport?.lng"></div>
                                </div>
                            </div>

                            <div x-show="selectedReport?.lat && selectedReport?.lng" class="aspect-square bg-slate-100 rounded-xl border border-slate-200 overflow-hidden relative group">
                                <div id="map-detail" class="w-full h-full z-10"></div>
                            </div>
                            
                            <div x-show="!selectedReport?.lat || !selectedReport?.lng" class="aspect-square bg-slate-100 rounded-xl border border-slate-200 border-dashed flex flex-col items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl mb-2">map</span>
                                <span class="text-sm font-medium">Peta tidak tersedia</span>
                                <span class="text-xs">Koordinat lokasi belum disematkan.</span>
                            </div>
                        </div>

                        <!-- Card Validasi & Penugasan -->
                        <div class="bg-sidebar text-white border border-sidebar-active/30 rounded-2xl p-6 shadow-lg relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 w-32 h-32 bg-sidebar-active/20 rounded-full blur-2xl"></div>
                            
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">admin_panel_settings</span>
                                Tindak Lanjut
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Tentukan Prioritas</label>
                                    <select class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                                        <option value="" disabled selected>Pilih tingkat prioritas...</option>
                                        <option value="high">Tinggi (Urgent)</option>
                                        <option value="medium">Sedang</option>
                                        <option value="low">Rendah</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Tugaskan Petugas / Regu</label>
                                    <select class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                                        <option value="" disabled selected>Pilih regu atau petugas...</option>
                                        <option value="regu_a">Regu A - Ketertiban</option>
                                        <option value="regu_b">Regu B - Patroli</option>
                                    </select>
                                </div>
                                <button class="w-full py-2.5 bg-sidebar-active hover:bg-sidebar-hover text-white rounded-xl font-semibold text-sm transition-colors mt-2">
                                    Validasi &amp; Teruskan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= VIEW: CREATE NEW ======================= -->
        <div x-show="view === 'create'" x-transition.opacity.duration.300ms style="display: none;">
             <div class="max-w-4xl mx-auto">
                 <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'list'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold font-headline text-slate-900" x-text="isEdit ? 'Edit Laporan' : 'Buat Laporan Baru'"></h2>
                        <p class="text-sm text-slate-500 mt-1" x-text="isEdit ? 'Ubah informasi laporan di bawah ini.' : 'Masukkan data pengaduan yang diterima secara offline atau media sosial.'"></p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 md:p-8 space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Pelapor -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Pelapor <span class="text-red-500">*</span></label>
                                <input type="text" placeholder="Masukkan nama pelapor" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <!-- Nomor Telepon -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor HP / Telepon</label>
                                <input type="tel" placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sumber Laporan -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Sumber Laporan <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    <label class="flex-1 flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-sidebar-active has-[:checked]:bg-sidebar-active/5 has-[:checked]:text-sidebar-active">
                                        <input type="radio" name="sumber" value="langsung" class="hidden" x-model="formSumber">
                                        <span class="material-symbols-outlined text-[20px]">person_pin</span>
                                        <span class="text-sm font-medium">Langsung (Offline)</span>
                                    </label>
                                    <label class="flex-1 flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-sidebar-active has-[:checked]:bg-sidebar-active/5 has-[:checked]:text-sidebar-active">
                                        <input type="radio" name="sumber" value="medsos" class="hidden" x-model="formSumber">
                                        <span class="material-symbols-outlined text-[20px]">public</span>
                                        <span class="text-sm font-medium">Media Sosial</span>
                                    </label>
                                </div>

                                <!-- Dropdown Platform Medsos -->
                                <div x-show="formSumber === 'medsos'" x-transition class="mt-4">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Platform Media Sosial <span class="text-red-500">*</span></label>
                                    <select class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm appearance-none">
                                        <option value="" disabled selected>Pilih platform...</option>
                                        <option value="whatsapp">WhatsApp</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="x_twitter">X (Twitter)</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <!-- Input NIK (Langsung) -->
                                <div x-show="formSumber === 'langsung'" x-transition class="mt-4">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">NIK Pelapor <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                                    <input type="text" placeholder="Masukkan 16 digit NIK" maxlength="16" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                                <select class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm appearance-none">
                                    <option value="" disabled selected>Pilih kategori pelanggaran...</option>
                                    <option value="ketertiban">Ketertiban Umum (PKL, dll)</option>
                                    <option value="bangunan">Bangunan Liar / Tanpa Izin</option>
                                    <option value="lingkungan">Pencemaran Lingkungan / Sampah</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tanggal Pelaporan -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Kejadian / Pelaporan <span class="text-red-500">*</span></label>
                                <input type="date" x-model="formTanggal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <!-- Waktu / Jam Pelaporan -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Kejadian <span class="text-red-500">*</span></label>
                                <input type="time" x-model="formJam" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Kejadian <span class="text-red-500">*</span></label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Alamat Manual -->
                                <div class="md:col-span-2 relative">
                                    <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400">location_on</span>
                                    <input type="text" x-model="formLokasi" placeholder="Tulis alamat atau detail lokasi..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                                </div>
                                <!-- Koordinat (Readonly) -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Latitude</label>
                                    <input type="text" x-model="formLat" readonly placeholder="Pilih dari map" class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Longitude</label>
                                    <input type="text" x-model="formLng" readonly placeholder="Pilih dari map" class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-600 outline-none cursor-not-allowed">
                                </div>
                            </div>

                            <div class="flex justify-end mb-2">
                                <button type="button" @click="getCurrentLocation()" class="text-xs font-medium text-sidebar-active hover:text-sidebar-hover flex items-center gap-1 bg-sidebar-active/10 px-2.5 py-1.5 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">my_location</span>
                                    Gunakan lokasi saat ini
                                </button>
                            </div>

                            <!-- Peta Interaktif -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden relative group">
                                <div id="map-picker" class="w-full h-[400px] bg-slate-100 z-10"></div>
                                <div class="absolute top-3 right-3 z-[1000]">
                                    <span class="bg-white/90 backdrop-blur text-xs font-medium px-3 py-1.5 rounded-lg shadow-sm border border-slate-200 text-slate-600 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">touch_app</span>
                                        Klik peta untuk tentukan titik
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan / Deskripsi Laporan <span class="text-red-500">*</span></label>
                            <textarea rows="4" placeholder="Ceritakan detail kejadian yang dilaporkan..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-none"></textarea>
                        </div>
                        
                        <!-- Upload Bukti -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Bukti (Opsional)</label>
                            
                            <!-- Dropzone / Input Area -->
                            <label class="block border-2 border-dashed border-slate-300 bg-slate-50 rounded-2xl p-8 flex flex-col items-center justify-center text-center hover:bg-slate-100 transition-colors cursor-pointer mb-4 relative">
                                <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">cloud_upload</span>
                                <p class="text-sm font-medium text-slate-700 mb-1">Klik atau seret untuk unggah foto</p>
                                <p class="text-xs text-slate-500">Bisa memilih lebih dari 1 foto. Maks. 5MB per foto (JPG, PNG)</p>
                                <!-- Multi File Input -->
                                <input type="file" multiple accept="image/jpeg, image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleFileUpload($event)">
                            </label>

                            <!-- Previews Grid -->
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4" x-show="previewPhotos.length > 0">
                                <template x-for="(photo, index) in previewPhotos" :key="index">
                                    <div class="relative aspect-square rounded-xl border border-slate-200 overflow-hidden group bg-slate-100 shadow-sm">
                                        <img :src="photo.url" class="w-full h-full object-cover">
                                        
                                        <!-- Overlay info & Remove button -->
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                            <div class="flex justify-end">
                                                <button type="button" @click="removePhoto(index)" class="w-6 h-6 bg-white hover:bg-red-50 hover:text-red-600 text-slate-700 rounded-full flex items-center justify-center shadow-md transition-colors" title="Hapus foto">
                                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                                </button>
                                            </div>
                                            <div class="text-[10px] text-white font-medium truncate" x-text="photo.name"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                        <button @click="view = 'list'" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
                            Batal
                        </button>
                        <button @click="saveNewReport()" class="px-6 py-2.5 bg-sidebar-active hover:bg-sidebar-hover text-white rounded-xl font-semibold text-sm transition-colors shadow-md flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span x-text="isEdit ? 'Perbarui Laporan' : 'Simpan Laporan'"></span>
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
        function pengaduanManager() {
            return {
                view: 'list', // 'list', 'detail', 'create'
                
                isEdit: false,
                editId: null,

                filterTab: 'semua', // 'semua', 'urgent', 'tidak_urgent'
                filterDateStart: '',
                filterDateEnd: '',
                selectedReport: null,
                previewPhotos: [], // Array untuk menyimpan foto yang akan diupload
                formSumber: 'langsung', // State untuk radio button sumber laporan
                formTanggal: '',
                formJam: '',
                
                formLokasi: '',
                formLat: '',
                formLng: '',
                mapInstance: null,
                markerInstance: null,
                mapDetailInstance: null,
                
                reports: [
                    { 
                        id: '#TKT-2023-0801', 
                        pelapor: 'Budi Santoso', 
                        no_hp: '081234567890',
                        nik: '3171234567890001',
                        sumber: 'medsos',
                        platform: 'whatsapp',
                        kategori: 'Ketertiban Umum', 
                        lokasi: 'Jl. Merdeka No. 10, Gambir, Jakarta Pusat', 
                        lat: '-6.175110',
                        lng: '106.827153',
                        deskripsi: 'Terdapat banyak PKL yang berjualan di trotoar sehingga mengganggu pejalan kaki. Mohon segera ditertibkan karena memakan seluruh bahu jalan.',
                        status: 'Baru', 
                        prioritas: 'High', 
                        is_read: false, 
                        tanggal: '2023-10-12',
                        jam: '14:30 WIB'
                    },
                    { 
                        id: '#TKT-2023-0802', 
                        pelapor: 'Siti Aminah', 
                        no_hp: '085712341234',
                        nik: '',
                        sumber: 'langsung',
                        platform: '',
                        kategori: 'Pencemaran Lingkungan / Sampah', 
                        lokasi: 'Pasar Baru, Jakarta Pusat', 
                        lat: '',
                        lng: '',
                        deskripsi: 'Tumpukan sampah di pinggir jalan dekat pintu masuk pasar sudah mulai berbau dan mengganggu aktivitas warga.',
                        status: 'Diproses', 
                        prioritas: 'Medium', 
                        is_read: true, 
                        tanggal: '2023-10-11',
                        jam: '09:15 WIB'
                    },
                    { 
                        id: '#TKT-2023-0803', 
                        pelapor: 'Ahmad Dahlan', 
                        no_hp: '',
                        nik: '3171234567890003',
                        sumber: 'medsos',
                        platform: 'instagram',
                        kategori: 'Bangunan Liar / Tanpa Izin', 
                        lokasi: 'Kawasan Monas, Jakarta Pusat', 
                        lat: '-6.182310',
                        lng: '106.832153',
                        deskripsi: 'Ada tenda liar yang dibangun di area taman utara Monas sejak kemarin malam.',
                        status: 'Selesai', 
                        prioritas: 'Low', 
                        is_read: true, 
                        tanggal: '2023-10-10',
                        jam: '20:45 WIB'
                    }
                ],
                
                init() {
                    // Watch for date filter changes
                    this.$watch('filterDateStart', value => console.log('Mulai dari:', value));
                    this.$watch('filterDateEnd', value => console.log('Sampai:', value));
                },

                filteredReports() {
                    let result = this.reports;

                    // Filter by Tab
                    if (this.filterTab === 'urgent') {
                        result = result.filter(r => r.prioritas === 'High');
                    } else if (this.filterTab === 'tidak_urgent') {
                        result = result.filter(r => r.prioritas !== 'High');
                    }

                    // Filter by Date Range
                    if (this.filterDateStart) {
                        result = result.filter(r => r.tanggal >= this.filterDateStart);
                    }
                    if (this.filterDateEnd) {
                        result = result.filter(r => r.tanggal <= this.filterDateEnd);
                    }

                    return result;
                },

                countUrgent() {
                    return this.reports.filter(r => r.prioritas === 'High').length;
                },
                
                countTidakUrgent() {
                    return this.reports.filter(r => r.prioritas !== 'High').length;
                },

                openDetail(item) {
                    // Jika laporan belum dibaca, tandai sudah dibaca
                    if (!item.is_read) {
                        item.is_read = true;
                    }
                    this.selectedReport = item;
                    this.view = 'detail';
                    window.scrollTo(0,0);

                    // Initialize Map for Detail View if coordinates exist
                    if (item.lat && item.lng) {
                        setTimeout(() => {
                            if (this.mapDetailInstance) {
                                this.mapDetailInstance.remove();
                            }
                            
                            const latlng = [parseFloat(item.lat), parseFloat(item.lng)];
                            this.mapDetailInstance = L.map('map-detail', {
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
                    // Reset state form baru
                    this.isEdit = false;
                    this.editId = null;
                    this.formSumber = 'langsung';
                    this.formTanggal = '';
                    this.formJam = '';
                    this.formLokasi = '';
                    this.formLat = '';
                    this.formLng = '';
                    if (this.markerInstance) {
                        this.mapInstance.removeLayer(this.markerInstance);
                        this.markerInstance = null;
                    }
                    
                    // Reset foto saat membuka form baru
                    this.previewPhotos.forEach(p => URL.revokeObjectURL(p.url));
                    this.previewPhotos = [];
                    
                    this.view = 'create';
                    window.scrollTo(0,0);
                    
                    // Initialize Map after DOM is visible
                    setTimeout(() => {
                        this.initMap();
                    }, 300);
                },

                editPengaduan(item) {
                    this.isEdit = true;
                    this.editId = item.id;
                    
                    // Populate data (some fields might be dummy depending on list item structure)
                    this.formSumber = item.sumber || 'langsung';
                    this.formTanggal = item.tanggal || '';
                    // Some items might not have 'jam', extract from time part if needed, or use mock
                    this.formJam = item.jam ? item.jam.replace(' WIB', '') : '';
                    this.formLokasi = item.lokasi || '';
                    this.formLat = item.lat || '';
                    this.formLng = item.lng || '';
                    
                    if (this.markerInstance && this.mapInstance) {
                        this.mapInstance.removeLayer(this.markerInstance);
                        this.markerInstance = null;
                    }
                    
                    this.previewPhotos.forEach(p => URL.revokeObjectURL(p.url));
                    this.previewPhotos = [];
                    
                    this.view = 'create';
                    window.scrollTo(0,0);
                    
                    setTimeout(() => {
                        this.initMap();
                        // If coordinates exist, set marker
                        if (this.formLat && this.formLng && this.mapInstance) {
                            const latlng = [parseFloat(this.formLat), parseFloat(this.formLng)];
                            this.mapInstance.setView(latlng, 15);
                            this.markerInstance = L.marker(latlng).addTo(this.mapInstance);
                        }
                    }, 300);
                },

                deletePengaduan(id) {
                    if (confirm('Yakin ingin menghapus pengaduan ini?')) {
                        this.reports = this.reports.filter(r => r.id !== id);
                    }
                },

                initMap() {
                    if (this.mapInstance) {
                        this.mapInstance.invalidateSize();
                        return;
                    }

                    // Default to Monas / Jakarta Pusat
                    const defaultCenter = [-6.175110, 106.827153];
                    
                    this.mapInstance = L.map('map-picker').setView(defaultCenter, 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapInstance);

                    this.mapInstance.on('click', (e) => {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;
                        
                        this.formLat = lat.toFixed(6);
                        this.formLng = lng.toFixed(6);

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
                    
                    // Optional: Show loading state here if needed
                    
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            this.formLat = lat.toFixed(6);
                            this.formLng = lng.toFixed(6);
                            
                            const latlng = [lat, lng];
                            
                            if (this.mapInstance) {
                                this.mapInstance.setView(latlng, 16); // Zoom lebih dekat ke lokasi
                                
                                if (this.markerInstance) {
                                    this.markerInstance.setLatLng(latlng);
                                } else {
                                    this.markerInstance = L.marker(latlng).addTo(this.mapInstance);
                                }
                            }
                        },
                        (error) => {
                            alert("Gagal mengambil lokasi. Pastikan izin akses lokasi (GPS) di browser diaktifkan.");
                        }
                    );
                },
                
                handleFileUpload(event) {
                    const files = event.target.files;
                    if (!files) return;
                    
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        // Opsional: Validasi ukuran file (misal 5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert(`File ${file.name} terlalu besar. Maksimal 5MB.`);
                            continue;
                        }
                        
                        // Buat object URL untuk preview
                        const url = URL.createObjectURL(file);
                        this.previewPhotos.push({
                            file: file,
                            url: url,
                            name: file.name
                        });
                    }
                    
                    // Reset input agar bisa memilih file yang sama lagi jika dihapus
                    event.target.value = '';
                },

                removePhoto(index) {
                    URL.revokeObjectURL(this.previewPhotos[index].url);
                    this.previewPhotos.splice(index, 1);
                },

                saveNewReport() {
                    // Mock save action
                    if (this.isEdit) {
                        console.log('Memperbarui laporan:', this.editId);
                        alert('Laporan berhasil diperbarui (Prototype)');
                    } else {
                        console.log('Menyimpan laporan baru dengan ' + this.previewPhotos.length + ' foto bukti.');
                        alert('Laporan berhasil disimpan (Prototype)');
                    }
                    this.view = 'list';
                }
            }
        }
    </script>
@endsection
