@props(['prefix'])

<div class="mb-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
    <label for="{{ $prefix }}-location-search" class="sr-only">Cari nama tempat atau alamat</label>
    <div class="relative">
        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[19px] text-slate-400">search</span>
        <input id="{{ $prefix }}-location-search" type="search" autocomplete="off" placeholder="Cari nama tempat atau alamat..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
    </div>
    <button id="{{ $prefix }}-location-search-button" type="button" class="rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sidebar-hover disabled:pointer-events-none">
        Cari Lokasi
    </button>
    <button id="{{ $prefix }}-current-location-button" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-sidebar-active/30 bg-white px-4 py-2.5 text-sm font-semibold text-sidebar-active transition hover:bg-blue-50 disabled:pointer-events-none">
        <span class="material-symbols-outlined text-[19px]">my_location</span>
        Lokasi Saat Ini
    </button>
</div>
<p id="{{ $prefix }}-location-status" class="mb-2 min-h-5 text-xs text-slate-500" aria-live="polite"></p>
