@extends('layouts.admin')

@section('title', 'Profil Pengguna')

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div x-data="profileManager()" x-cloak>
        
        <div x-show="view === 'detail'" x-transition.opacity.duration.300ms>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Profil Pengguna</h1>
                    <p class="text-sm text-slate-500 mt-1">Kelola informasi pribadi, identitas jabatan, dan preferensi akun Anda.</p>
                </div>
                <div>
                    <button @click="openEdit()" type="button" class="px-4 py-2.5 bg-sidebar-active hover:bg-sidebar-hover rounded-xl text-sm font-semibold text-white shadow-md transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        <span>Edit Profil</span>
                    </button>
                </div>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="h-32 bg-gradient-to-r from-slate-800 to-slate-900 w-full relative"></div>
                <div class="px-6 pb-6 relative flex flex-col md:flex-row items-center md:items-end justify-between gap-4 -mt-12">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-5 text-center md:text-left">
                        <div class="w-24 h-24 rounded-2xl bg-slate-100 border-4 border-white shadow-md flex items-center justify-center text-slate-400 shrink-0">
                            <span class="material-symbols-outlined text-[48px]">person</span>
                        </div>
                        <div class="mb-1">
                            <h2 class="text-2xl font-bold text-slate-900 font-headline" x-text="user.nama"></h2>
                            <p class="text-sm font-semibold text-sidebar-active" x-text="user.jabatan"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Status: Aktif
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">analytics</span>
                            Statistik Kinerja
                        </h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tahun Aktif Kerja</div>
                                    <div class="text-xl font-bold text-slate-800 mt-0.5" x-text="user.tahunMulai + ' (' + hitungMasaKerja(user.tahunMulai) + ' Thn)'"></div>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">badge</span>
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Dokumen / Arsip</div>
                                    <div class="text-xl font-bold text-slate-800 mt-0.5" x-text="stats.totalDokumen + ' File'"></div>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">folder</span>
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Aktivitas Bulan Ini</div>
                                    <div class="text-xl font-bold text-slate-800 mt-0.5" x-text="stats.aktivitasBulanIni + ' Log'"></div>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">history</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">person_outline</span>
                            Informasi Detail Pegawai
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</div>
                                <div class="text-sm font-semibold text-slate-800" x-text="user.nama"></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">NIP / ID Pegawai</div>
                                <div class="text-sm font-semibold text-slate-800" x-text="user.nip"></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Jabatan</div>
                                <div class="text-sm font-semibold text-slate-800" x-text="user.jabatan"></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Unit Kerja / Divisi</div>
                                <div class="text-sm font-semibold text-slate-800" x-text="user.unitKerja"></div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Institusi</div>
                                <div class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-slate-400">mail</span>
                                    <span x-text="user.email"></span>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor Telepon</div>
                                <div class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-slate-400">call</span>
                                    <span x-text="user.telepon"></span>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat Kantor / Domisili</div>
                                <div class="text-sm font-medium text-slate-700 leading-relaxed" x-text="user.alamat"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div x-show="view === 'edit'" x-transition.opacity.duration.300ms style="display: none;">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'detail'" class="p-2 bg-white border border-slate-200 rounded-full text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold font-headline text-slate-900">Edit Profil Pengguna</h2>
                        <p class="text-sm text-slate-500 mt-1">Ubah data informasi pribadi dan rincian kontak Anda.</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 md:p-8 space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formUser.nama" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">NIP / ID Pegawai</label>
                                <input type="text" x-model="formUser.nip" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formUser.jabatan" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Unit Kerja / Divisi</label>
                                <input type="text" x-model="formUser.unitKerja" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email <span class="text-red-500">*</span></label>
                                <input type="email" x-model="formUser.email" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon</label>
                                <input type="text" x-model="formUser.telepon" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Mulai Kerja</label>
                                <input type="number" x-model="formUser.tahunMulai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat</label>
                                <textarea x-model="formUser.alamat" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm resize-y"></textarea>
                            </div>
                        </div>

                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 z-50">
                        <button @click="view = 'detail'" class="px-6 py-2.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 rounded-xl font-semibold text-sm transition-colors">
                            Batal
                        </button>
                        <button @click="saveProfile()" class="px-6 py-2.5 bg-sidebar-active hover:bg-sidebar-hover text-white rounded-xl font-semibold text-sm transition-colors shadow-md flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Perubahan</span>
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
        function profileManager() {
            return {
                view: 'detail', // 'detail', 'edit'
                
                // Data Profil Utama
                user: {
                    nama: 'Budi Santoso, S.STP',
                    nip: '19880312 201001 1 002',
                    jabatan: 'Kepala Sub Bagian Operasional',
                    unitKerja: 'Satuan Polisi Pamong Praja',
                    email: 'budi.santoso@satpolpp.go.id',
                    telepon: '+62 812-3456-7890',
                    tahunMulai: 2015,
                    alamat: 'Jl. Merdeka Selatan No. 8-9, Gambir, Jakarta Pusat'
                },

                // Dummy Statistik Kinerja
                stats: {
                    totalDokumen: 142,
                    aktivitasBulanIni: 28
                },

                // Temp State Form
                formUser: {},

                hitungMasaKerja(tahun) {
                    const tahunSekarang = new Date().getFullYear();
                    return Math.max(0, tahunSekarang - tahun);
                },

                openEdit() {
                    // Copy data user ke form (shallow copy)
                    this.formUser = { ...this.user };
                    this.view = 'edit';
                    window.scrollTo(0, 0);
                },

                saveProfile() {
                    if (!this.formUser.nama || !this.formUser.jabatan || !this.formUser.email) {
                        alert('Silakan lengkapi field wajib (Nama, Jabatan, dan Email).');
                        return;
                    }

                    this.user = { ...this.formUser };
                    this.view = 'detail';
                    window.scrollTo(0, 0);
                    window.tampilToast('Edit berhasil! Profil berhasil diperbarui.');
                }
            }
        }
    </script>
@endsection