@extends('layouts.super_admin')

@section('title', 'Manajemen User')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-bold text-slate-900 tracking-tight">Manajemen User</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola akun admin untuk mengakses seluruh platform.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-super-active px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-brand-600">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            Tambah User
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-super-active/10 text-super-active flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">group</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Total User</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-total">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">admin_panel_settings</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Admin Satpol PP</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-admin">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">block</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Terblokir</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-terblokir">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">shield_person</span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">User Aktif</p>
                    <p class="font-headline text-2xl font-bold text-slate-900" id="stat-aktif">0</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
            <div class="flex gap-2 flex-wrap">
                <button type="button" data-tab="semua" onclick="setTab('semua')"
                    class="tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors">Semua</button>
            </div>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">Database</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">User</th>
                        <th class="px-6 py-3 font-semibold">Username</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold">Dibuat</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-list"></tbody>
            </table>
        </div>

        <div id="kosong" class="hidden px-6 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">👤</div>
            <p>Belum ada user dalam kategori ini.</p>
        </div>
    </div>

    {{-- Modal tambah/ubah user --}}
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-headline font-bold text-slate-800" id="modal-judul">Tambah User</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="user-form" class="p-6 space-y-4 overflow-y-auto" onsubmit="simpanUser(event)">
                <input type="hidden" name="edit_index" value="">
                <input type="hidden" name="_method" value="POST">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Andi Saputra"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required placeholder="Contoh: andi.saputra"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" placeholder="andi@example.com"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kata Sandi <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter"
                        minlength="8" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:border-super-active focus:ring-1 focus:ring-super-active outline-none">
                    <p id="pw-hint" class="text-xs text-slate-400 mt-1 hidden">Biarkan kosong jika tidak ingin mengubah kata sandi.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupModal()" class="border border-slate-300 text-slate-600 px-5 py-2 rounded-xl font-medium hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="bg-super-active text-white px-6 py-2 rounded-xl font-medium hover:bg-brand-600 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('scripts')
<script>
    const databaseUsers = @json($users);
    let tab = 'semua';

    function getUsers() {
        return databaseUsers;
    }

    function saveAll(data) { databaseUsers.splice(0, databaseUsers.length, ...data); }

    function statusClass(s) {
        return s === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200';
    }

    function filtered(data) {
        return data;
    }

    function setTab(t) {
        tab = t;
        document.querySelectorAll('.tab-btn').forEach(b => {
            const active = b.dataset.tab === t;
            b.className = 'tab-btn text-sm font-semibold px-4 py-2 rounded-xl border transition-colors ' +
                (active ? 'bg-super-active text-white border-super-active' : 'border-slate-200 text-slate-600 hover:bg-slate-50');
        });
        renderList();
    }

    function renderList() {
        const all = getUsers();
        document.getElementById('stat-total').textContent = all.length;
        document.getElementById('stat-admin').textContent = all.filter(u => u.role === 'admin').length;
        document.getElementById('stat-terblokir').textContent = all.filter(u => u.status === 'blocked').length;
        document.getElementById('stat-aktif').textContent = all.filter(u => u.status === 'active').length;

        const list = filtered(all);
        const tbody = document.getElementById('user-list');
        const kosong = document.getElementById('kosong');

        if (list.length === 0) {
            tbody.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        tbody.innerHTML = list.map((u, i) => {
            const gi = all.findIndex(x => x.username === u.username && x.nama === u.nama);
            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-super-active/10 text-super-active flex items-center justify-center font-bold text-xs flex-shrink-0">
                                ${u.nama.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
                            </div>
                            <div>
                                <div class="font-medium text-slate-800">${u.nama}</div>
                                <div class="text-xs text-slate-400">${u.email || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3.5 text-slate-500">${u.username}</td>
                    <td class="px-6 py-3.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full border ${statusClass(u.status)}">${u.status === 'active' ? 'Aktif' : 'Terblokir'}</span>
                    </td>
                    <td class="px-6 py-3.5 text-slate-500">${u.dibuat || '-'}</td>
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <button type="button" onclick="toggleStatus(${gi})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Ubah status">
                            <span class="material-symbols-outlined text-[20px]">${u.status === 'active' ? 'block' : 'check_circle'}</span>
                        </button>
                        <button type="button" onclick="bukaEdit(${gi})" class="p-1.5 text-slate-400 hover:text-super-active hover:bg-super-active/10 rounded-lg transition-colors" title="Edit">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button type="button" onclick="hapusUser(${gi})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function bukaTambah() {
        document.getElementById('modal-judul').textContent = 'Tambah User';
        document.getElementById('user-form').reset();
        document.querySelector('[name=edit_index]').value = '';
        document.querySelector('[name=_method]').value = 'POST';
        document.getElementById('pw-hint').classList.add('hidden');
        document.querySelector('[name=password]').required = true;
        document.getElementById('modal').classList.remove('hidden');
    }

    function bukaEdit(gi) {
        const u = getUsers()[gi];
        const f = document.getElementById('user-form');
        f.reset();
        document.getElementById('modal-judul').textContent = 'Ubah User';
        document.querySelector('[name=edit_index]').value = gi;
        document.querySelector('[name=_method]').value = 'PUT';
        f.nama.value = u.nama;
        f.username.value = u.username;
        f.email.value = u.email || '';
        f.password.required = false;
        document.getElementById('pw-hint').classList.remove('hidden');
        document.getElementById('modal').classList.remove('hidden');
    }

    function tutupModal() { document.getElementById('modal').classList.add('hidden'); }

    function simpanUser(e) {
        e.preventDefault();
        const f = document.getElementById('user-form');
        const gi = document.querySelector('[name=edit_index]').value;
        f.action = gi === '' ? @json(route('super_admin.user.store')) : @json(route('super_admin.user.update', ['user' => '__USER__'])).replace('__USER__', getUsers()[gi].id);
        const submitButton = f.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.classList.add('cursor-wait', 'opacity-70');
        submitButton.textContent = 'Memproses...';
        f.submit();
    }

    function toggleStatus(gi) {
        const all = getUsers();
        const user = all[gi];
        window.konfirmasiHapus({
            title: 'Konfirmasi Perubahan Status',
            message: `Ubah status akun "${user.nama}"? Perubahan ini memengaruhi akses login akun.`,
            confirmText: 'Ya, Ubah Status',
            onConfirm: () => {
                fetch(@json(route('super_admin.user.status', ['user' => '__USER__'])).replace('__USER__', user.id), {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                }).then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Status akun gagal diubah.');
                    const statusLabel = data.status === 'active' ? 'Aktif' : 'Diblokir';
                    window.tampilkanToastLaluMuatUlang(`Status akun berhasil diubah menjadi ${statusLabel}.`);
                }).catch(error => window.tampilToast(error.message, 'error'));
            },
        });
    }

    function hapusUser(gi) {
        const all = getUsers();
        const nama = all[gi] ? `user "${all[gi].nama}"` : 'user ini';
        window.konfirmasiHapus({
            title: 'Hapus User',
            message: `Apakah Anda yakin ingin menghapus ${nama}? Akun ini tidak akan dapat login kembali.`,
            onConfirm: () => {
                fetch(@json(route('super_admin.user.destroy', ['user' => '__USER__'])).replace('__USER__', all[gi].id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                }).then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'User gagal dihapus.');
                    window.tampilkanToastLaluMuatUlang(data.message || 'User berhasil dihapus.');
                }).catch(error => window.tampilToast(error.message, 'error'));
            }
        });
    }



    setTab('semua');
</script>
@endpush
