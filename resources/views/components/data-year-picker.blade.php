@props(['activeDataYear', 'availableDataYears', 'mustSelectDataYear'])

<button type="button" id="open-data-year" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" aria-haspopup="dialog" aria-controls="data-year-dialog">
    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
    Data {{ $activeDataYear }}
</button>

<dialog id="data-year-dialog" aria-labelledby="data-year-title" class="m-auto w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-slate-800 shadow-xl backdrop:bg-slate-900/60">
    <h2 id="data-year-title" class="font-headline text-xl font-bold">Pilih Tahun Data</h2>
    <p class="mt-2 text-sm text-slate-500">Pilih tahun untuk menampilkan data pada seluruh menu.</p>
    <form method="POST" action="{{ route('data-year.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="data-year" class="mb-2 block text-sm font-medium">Tahun data</label>
            <select id="data-year" name="data_year" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">
                @foreach ($availableDataYears as $year)
                    <option value="{{ $year }}" @selected($activeDataYear === $year)>{{ $year }}</option>
                @endforeach
            </select>
            @error('data_year')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end gap-3">
            @unless ($mustSelectDataYear)
                <button id="close-data-year" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium">Batal</button>
            @endunless
            <button type="submit" class="rounded-xl bg-sidebar-active px-5 py-2 text-sm font-semibold text-white hover:bg-sidebar-hover">Pilih</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('data-year-dialog');
        const required = @json($mustSelectDataYear);
        document.getElementById('open-data-year').addEventListener('click', () => dialog.showModal());
        document.getElementById('close-data-year')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('cancel', (event) => {
            if (required) event.preventDefault();
        });
        if (required || @json($errors->has('data_year'))) dialog.showModal();
    })();
</script>
