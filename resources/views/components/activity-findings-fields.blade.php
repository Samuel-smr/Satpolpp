@props(['findings' => [], 'prefix' => 'activity'])

@php
    $findingRows = array_values($findings);
@endphp

<section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="font-headline text-lg font-bold text-slate-800">Temuan</h2>
            <p class="mt-1 text-sm text-slate-500">Tambahkan satu atau beberapa temuan jika ada.</p>
        </div>
        <button id="{{ $prefix }}-add-finding" type="button" class="inline-flex items-center gap-2 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sidebar-hover">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Temuan
        </button>
    </div>

    <div id="{{ $prefix }}-findings" class="mt-5 space-y-4">
        @foreach ($findingRows as $index => $finding)
            <div data-finding-row class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p data-finding-title class="text-sm font-semibold text-slate-700">Temuan {{ $index + 1 }}</p>
                    <button type="button" data-remove-finding class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600" title="Hapus temuan">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis temuan</label>
                        <select name="findings[{{ $index }}][type]" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih jenis</option>
                            @foreach (\App\FindingType::cases() as $findingType)
                                <option value="{{ $findingType->value }}" @selected(($finding['type'] ?? null) === $findingType->value)>{{ $findingType->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jumlah temuan</label>
                        <input name="findings[{{ $index }}][quantity]" type="number" min="1" step="1" required value="{{ $finding['quantity'] ?? '' }}" placeholder="Jumlah" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Satuan temuan</label>
                        <select name="findings[{{ $index }}][unit]" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih satuan</option>
                            @foreach (\App\FindingUnit::cases() as $findingUnit)
                                <option value="{{ $findingUnit->value }}" @selected(($finding['unit'] ?? null) === $findingUnit->value)>{{ $findingUnit->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <template id="{{ $prefix }}-finding-template">
        <div data-finding-row class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p data-finding-title class="text-sm font-semibold text-slate-700"></p>
                <button type="button" data-remove-finding class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600" title="Hapus temuan">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis temuan</label>
                    <select data-finding-name="type" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                        <option value="">Pilih jenis</option>
                        @foreach (\App\FindingType::cases() as $findingType)
                            <option value="{{ $findingType->value }}">{{ $findingType->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jumlah temuan</label>
                    <input data-finding-name="quantity" type="number" min="1" step="1" required placeholder="Jumlah" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Satuan temuan</label>
                    <select data-finding-name="unit" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-sidebar-active focus:outline-none focus:ring-1 focus:ring-sidebar-active">
                        <option value="">Pilih satuan</option>
                        @foreach (\App\FindingUnit::cases() as $findingUnit)
                            <option value="{{ $findingUnit->value }}">{{ $findingUnit->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </template>
</section>

@push('scripts')
    <script>
        (() => {
            const container = document.getElementById(@js($prefix.'-findings'));
            const template = document.getElementById(@js($prefix.'-finding-template'));
            const addButton = document.getElementById(@js($prefix.'-add-finding'));
            let nextIndex = container?.querySelectorAll('[data-finding-row]').length ?? 0;

            const refreshTitles = () => {
                container?.querySelectorAll('[data-finding-row]').forEach((row, index) => {
                    row.querySelector('[data-finding-title]').textContent = `Temuan ${index + 1}`;
                });
            };

            container?.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-finding]');

                if (removeButton) {
                    removeButton.closest('[data-finding-row]').remove();
                    refreshTitles();
                }
            });

            addButton?.addEventListener('click', () => {
                const row = template.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[data-finding-name]').forEach((input) => {
                    input.name = `findings[${nextIndex}][${input.dataset.findingName}]`;
                });
                nextIndex += 1;
                container.appendChild(row);
                refreshTitles();
            });

            refreshTitles();
        })();
    </script>
@endpush
