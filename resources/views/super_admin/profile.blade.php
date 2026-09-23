@extends('layouts.super_admin')

@section('title', 'Profil Super Admin')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Profil Super Admin</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola identitas dan keamanan akun Super Admin Anda.</p>
        </div>
        <button type="button" onclick="bukaEditProfil()"
            class="inline-flex items-center gap-2 rounded-xl bg-super-active px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-colors hover:bg-super-hover">
            <span class="material-symbols-outlined text-[18px]">edit</span>
            Edit Profil
        </button>
    </div>

    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-32 bg-gradient-to-r from-slate-800 to-slate-900"></div>
        <div class="relative -mt-12 flex flex-col items-center justify-between gap-4 px-6 pb-6 md:flex-row md:items-end">
            <div class="flex flex-col items-center gap-5 text-center md:flex-row md:items-end md:text-left">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border-4 border-white bg-slate-100 text-slate-400 shadow-md">
                    <span class="material-symbols-outlined text-[48px]">person</span>
                </div>
                <div class="mb-1">
                    <h2 class="font-headline text-2xl font-bold text-slate-900">{{ $profileUser->name }}</h2>
                    <p class="text-sm font-semibold text-super-active">Super Administrator</p>
                </div>
            </div>
            <span @class([
                'flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider',
                'border-emerald-100 bg-emerald-50 text-emerald-600' => $profileUser->status === \App\UserStatus::Active,
                'border-red-100 bg-red-50 text-red-600' => $profileUser->status === \App\UserStatus::Blocked,
            ])>
                <span @class([
                    'h-2 w-2 rounded-full',
                    'bg-emerald-500' => $profileUser->status === \App\UserStatus::Active,
                    'bg-red-500' => $profileUser->status === \App\UserStatus::Blocked,
                ])></span>
                {{ $profileUser->status === \App\UserStatus::Active ? 'Aktif' : 'Diblokir' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Laporan Dibuat</p>
                        <p class="font-headline mt-1 text-2xl font-bold text-slate-800">{{ $profileStats['reports_created'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-500">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aktivitas Tercatat</p>
                        <p class="font-headline mt-1 text-2xl font-bold text-slate-800">{{ $profileStats['activities_logged'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-6 flex items-center gap-2 border-b border-slate-100 pb-4 text-sm font-bold uppercase tracking-wider text-slate-800">
                <span class="material-symbols-outlined text-[20px] text-slate-400">manage_accounts</span>
                Informasi Akun
            </h3>
            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Lengkap</dt>
                    <dd class="text-sm font-semibold text-slate-800">{{ $profileUser->name }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Username</dt>
                    <dd class="text-sm font-semibold text-slate-800">{{ $profileUser->username }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Email</dt>
                    <dd class="flex items-center gap-1.5 text-sm font-semibold text-slate-800">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">mail</span>
                        {{ $profileUser->email ?: 'Belum diisi' }}
                    </dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">No. WhatsApp</dt>
                    <dd class="flex items-center gap-1.5 text-sm font-semibold text-slate-800">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">phone</span>
                        {{ $profileUser->phone ?: 'Belum diisi' }}
                    </dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Akun Dibuat</dt>
                    <dd class="text-sm font-semibold text-slate-800">{{ $profileUser->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div id="profile-modal" @class([
        'fixed inset-0 z-50 flex items-center justify-center p-4',
        'hidden' => !$errors->any(),
    ])>
        <div class="absolute inset-0 bg-black/40" onclick="tutupEditProfil()"></div>
        <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="font-headline text-lg font-bold text-slate-800">Edit Profil Super Admin</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Perbarui informasi akun dan password Anda.</p>
                </div>
                <button type="button" onclick="tutupEditProfil()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('super_admin.profile.update') }}" class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="profile-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="profile-name" type="text" name="name" value="{{ old('name', $profileUser->name) }}" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="profile-username" class="mb-1.5 block text-sm font-semibold text-slate-700">Username <span class="text-red-500">*</span></label>
                        <input id="profile-username" type="text" name="username" value="{{ old('username', $profileUser->username) }}" required maxlength="100"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                        @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="profile-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                        <input id="profile-email" type="email" name="email" value="{{ old('email', $profileUser->email) }}" maxlength="255"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="profile-phone" class="mb-1.5 block text-sm font-semibold text-slate-700">No. WhatsApp</label>
                        <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $profileUser->phone) }}" maxlength="25" placeholder="Contoh: 08xxxxxxxxxx"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                        <p class="mt-1 text-xs text-slate-400">Nomor ini akan menerima notifikasi WhatsApp saat ada laporan baru.</p>
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="mb-4 flex items-start gap-2">
                        <span class="material-symbols-outlined text-[20px] text-slate-400">lock</span>
                        <p class="text-xs leading-relaxed text-slate-500">Biarkan seluruh bagian password kosong jika tidak ingin mengganti password.</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="current-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password Saat Ini</label>
                            <input id="current-password" type="password" name="current_password" autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                            @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="new-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password Baru</label>
                                <input id="new-password" type="password" name="password" autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                                <input id="password-confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 outline-none focus:border-super-active focus:ring-1 focus:ring-super-active">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" onclick="tutupEditProfil()" class="rounded-xl border border-slate-300 px-5 py-2.5 font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-super-active px-6 py-2.5 font-semibold text-white hover:bg-super-hover">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalProfil = document.getElementById('profile-modal');

        function bukaEditProfil() {
            modalProfil.classList.remove('hidden');
        }

        function tutupEditProfil() {
            modalProfil.classList.add('hidden');
        }
    </script>
@endpush