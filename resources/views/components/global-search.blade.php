<div class="relative w-full max-w-md" data-global-search data-search-url="{{ route('global-search') }}">
    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span>
    <input type="search" data-global-search-input autocomplete="off" aria-label="Pencarian global"
        placeholder="Cari laporan, kegiatan, atau petugas..."
        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-700 placeholder-slate-400 transition-all focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
    <div data-global-search-results class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-white py-2 shadow-xl"></div>
</div>
