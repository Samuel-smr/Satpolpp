@extends('layouts.admin')

@section('title', 'Evaluasi Kegiatan')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Evaluasi Kegiatan</h1>
            <p class="mt-1 text-sm text-slate-500">Catat hasil dan kendala kegiatan internal maupun kegiatan eksternal
                yang berasal dari laporan warga.</p>
        </div>

        <button type="button" onclick="bukaTambah()"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidebar-active px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-sidebar-hover">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Evaluasi
        </button>
    </div>

    <form method="GET" action="{{ route('admin.evaluasi') }}" class="mb-6 grid grid-cols-1 items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="evaluation-search">PENCARIAN</label><input id="evaluation-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kegiatan, laporan, hasil" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="evaluation-type">SUMBER</label><select id="evaluation-type" name="type" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Semua sumber</option>@foreach ($activityTypes as $type)<option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="evaluation-month">BULAN</label><input id="evaluation-month" type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="evaluation-start">MULAI</label><input id="evaluation-start" type="date" name="date_start" value="{{ $filters['date_start'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-500" for="evaluation-end">SAMPAI</label><input id="evaluation-end" type="date" name="date_end" value="{{ $filters['date_end'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-sidebar-active px-4 py-2 text-sm font-semibold text-white">Terapkan</button><a href="{{ route('admin.evaluasi') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm">Reset</a></div>
    </form>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Evaluasi', 'value' => $evaluationSummary['total'], 'icon' => 'assessment', 'icon_class' => 'bg-sidebar-active/10 text-sidebar-active'],
            ['label' => 'Internal', 'value' => $evaluationSummary['internal'], 'icon' => 'home_work', 'icon_class' => 'bg-emerald-50 text-emerald-600'],
            ['label' => 'Eksternal (Laporan)', 'value' => $evaluationSummary['external'], 'icon' => 'description', 'icon_class' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Ada Kendala', 'value' => $evaluationSummary['with_obstacles'], 'icon' => 'warning', 'icon_class' => 'bg-amber-50 text-amber-600'],
        ] as $stat)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat['icon_class'] }}">
                        <span class="material-symbols-outlined text-[24px]">{{ $stat['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="font-headline text-2xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="text-sm font-semibold text-slate-700">Daftar Evaluasi Kegiatan</span>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                Data Database
            </span>
        </div>

        @if ($evaluations->isEmpty())
            <div class="px-6 py-12 text-center text-slate-400">
                <span class="material-symbols-outlined mb-2 text-4xl">assessment</span>
                <p>Belum ada data evaluasi kegiatan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wider text-slate-400">
                            <th class="px-6 py-3 font-semibold">Kegiatan</th>
                            <th class="px-6 py-3 font-semibold">Sumber</th>
                            <th class="px-6 py-3 font-semibold">Hasil Evaluasi</th>
                            <th class="px-6 py-3 font-semibold">Kendala</th>
                            <th class="px-6 py-3 font-semibold">Dievaluasi</th>
                            <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluations as $evaluation)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-slate-800">{{ $evaluation->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $evaluation->report?->code ?? $evaluation->activity_date->format('d/m/Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span @class([
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $evaluation->type === \App\ActivityType::Internal,
                                        'border-blue-200 bg-blue-50 text-blue-700' => $evaluation->type === \App\ActivityType::External,
                                    ])>
                                        {{ $evaluation->type->label() }}
                                    </span>
                                </td>
                                <td class="max-w-sm px-6 py-3.5 text-slate-600">{{ $evaluation->evaluation_notes }}</td>
                                <td class="max-w-sm px-6 py-3.5 text-slate-600">
                                    @if ($evaluation->evaluation_obstacles)
                                        <div class="flex items-start gap-2">
                                            <span class="material-symbols-outlined mt-0.5 text-[18px] text-amber-500">warning</span>
                                            <span>{{ $evaluation->evaluation_obstacles }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-slate-500">
                                    <p>{{ $evaluation->evaluated_at->format('d/m/Y H:i') }}</p>
                                    <p class="mt-0.5 text-xs">{{ $evaluation->evaluatedByUser?->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    @can('evaluate-activity', $evaluation)
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            onclick="bukaEditEvaluasi(@js([
                                                'id' => $evaluation->id,
                                                'name' => $evaluation->name,
                                                'notes' => $evaluation->evaluation_notes,
                                                'obstacles' => $evaluation->evaluation_obstacles,
                                            ]))"
                                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-amber-50 hover:text-amber-600"
                                            title="Edit evaluasi">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.evaluasi.destroy', $evaluation) }}"
                                            data-confirm-delete="Hapus evaluasi kegiatan &quot;{{ $evaluation->name }}&quot;? Kegiatan dan laporan asal tidak akan dihapus dan kegiatan dapat dievaluasi kembali.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                                title="Hapus evaluasi">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                        <span class="text-xs text-slate-400">Lihat saja</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($evaluations->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $evaluations->links() }}
                </div>
            @endif
        @endif
    </div>

    <div id="modal" @class([
        'fixed inset-0 z-50 flex items-center justify-center p-4',
        'hidden' => !$errors->any() || $editingEvaluation !== null,
    ])>
        <div class="absolute inset-0 bg-black/40" onclick="tutupModal()"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="font-headline font-bold text-slate-800">Tambah Evaluasi</h2>
                <button type="button" onclick="tutupModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.evaluasi.store') }}" class="space-y-4 overflow-y-auto p-6">
                @csrf

                <div>
                    <label for="evaluation-source" class="mb-1 block text-sm font-medium">Sumber <span
                            class="text-red-500">*</span></label>
                    <select id="evaluation-source" onchange="filterKegiatan()"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        <option value="">Pilih Sumber</option>
                        <option value="internal">Internal (Kegiatan Satpol PP)</option>
                        <option value="external" @selected(old('activity_id') && $availableActivities->firstWhere('id', (int) old('activity_id'))?->type === \App\ActivityType::External)>
                            Eksternal (Dari Laporan)</option>
                    </select>
                </div>

                <div>
                    <label for="evaluation-category" class="mb-1 block text-sm font-medium">Kategori</label>
                    <select id="evaluation-category" onchange="filterKegiatan()"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        <option value="">Semua kategori</option>
                        @foreach ($availableCategories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="activity-id" class="mb-1 block text-sm font-medium">Nama Kegiatan <span
                            class="text-red-500">*</span></label>
                    <select name="activity_id" id="activity-id" required disabled
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                        <option value="">Pilih Sumber Terlebih Dahulu</option>
                        @foreach ($availableActivities as $activity)
                            <option value="{{ $activity->id }}" data-source="{{ $activity->type->value }}" data-category="{{ $activity->category ?? '' }}"
                                @selected((int) old('activity_id') === $activity->id)>
                                {{ $activity->name }} — {{ $activity->activity_date->format('d/m/Y') }}
                                @if ($activity->report)
                                    ({{ $activity->report->code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p id="activity-empty" class="mt-1 hidden text-xs text-slate-500">Belum ada kegiatan pada kategori ini.</p>
                    @error('activity_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="evaluation-notes" class="mb-1 block text-sm font-medium">Hasil Evaluasi <span
                            class="text-red-500">*</span></label>
                    <textarea name="evaluation_notes" id="evaluation-notes" rows="4" required maxlength="3000"
                        placeholder="Catat hasil pelaksanaan dan kondisi setelah kegiatan..."
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">{{ old('evaluation_notes') }}</textarea>
                    @error('evaluation_notes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="evaluation-obstacles" class="mb-1 block text-sm font-medium">Kendala</label>
                    <textarea name="evaluation_obstacles" id="evaluation-obstacles" rows="3" maxlength="3000"
                        placeholder="Isi jika terdapat kendala selama pelaksanaan..."
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">{{ old('evaluation_obstacles') }}</textarea>
                    @error('evaluation_obstacles')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupModal()"
                        class="rounded-xl border border-slate-300 px-5 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-xl bg-sidebar-active px-6 py-2 font-medium text-white transition-colors hover:bg-sidebar-hover">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-evaluation-modal" @class([
        'fixed inset-0 z-50 flex items-center justify-center p-4',
        'hidden' => !$errors->any() || $editingEvaluation === null,
    ])>
        <div class="absolute inset-0 bg-black/40" onclick="tutupEditEvaluasi()"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="font-headline font-bold text-slate-800">Edit Evaluasi</h2>
                    <p id="edit-evaluation-name" class="mt-0.5 text-sm text-slate-500"></p>
                </div>
                <button type="button" onclick="tutupEditEvaluasi()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="edit-evaluation-form" method="POST" action="" class="space-y-4 overflow-y-auto p-6">
                @csrf
                @method('PUT')
                <input id="evaluation-context-id" type="hidden" name="evaluation_context_id" value="{{ old('evaluation_context_id') }}">

                <div>
                    <label for="edit-evaluation-notes" class="mb-1 block text-sm font-medium">Hasil Evaluasi <span class="text-red-500">*</span></label>
                    <textarea name="evaluation_notes" id="edit-evaluation-notes" rows="4" required maxlength="3000"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active"></textarea>
                    @if ($editingEvaluation)
                        @error('evaluation_notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <div>
                    <label for="edit-evaluation-obstacles" class="mb-1 block text-sm font-medium">Kendala</label>
                    <textarea name="evaluation_obstacles" id="edit-evaluation-obstacles" rows="3" maxlength="3000"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active"></textarea>
                    @if ($editingEvaluation)
                        @error('evaluation_obstacles')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupEditEvaluasi()"
                        class="rounded-xl border border-slate-300 px-5 py-2 font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-xl bg-sidebar-active px-6 py-2 font-medium text-white hover:bg-sidebar-hover">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalEvaluasi = document.getElementById('modal');
        const sumberEvaluasi = document.getElementById('evaluation-source');
        const kategoriEvaluasi = document.getElementById('evaluation-category');
        const kegiatanEvaluasi = document.getElementById('activity-id');
        const pesanKegiatanKosong = document.getElementById('activity-empty');
        const semuaPilihanKegiatan = Array.from(kegiatanEvaluasi.options).slice(1);
        const modalEditEvaluasi = document.getElementById('edit-evaluation-modal');
        const formEditEvaluasi = document.getElementById('edit-evaluation-form');
        const polaUrlEditEvaluasi = @json(route('admin.evaluasi.update', ['activity' => '__ACTIVITY__']));

        function filterKegiatan() {
            const sumber = sumberEvaluasi.value;
            const kategori = kategoriEvaluasi.value;
            const nilaiLama = kegiatanEvaluasi.value;
            const sumberEksternal = sumber === 'external';

            kategoriEvaluasi.disabled = sumberEksternal;

            if (sumberEksternal) {
                kategoriEvaluasi.value = '';
            }

            semuaPilihanKegiatan.forEach((pilihan) => {
                const sesuaiSumber = pilihan.dataset.source === sumber;
                const sesuaiKategori = !kategori
                    || (kategori === '__uncategorized__' && !pilihan.dataset.category)
                    || pilihan.dataset.category === kategori;

                pilihan.hidden = !sesuaiSumber || !sesuaiKategori;
            });

            const pilihanTersedia = semuaPilihanKegiatan.filter((pilihan) => !pilihan.hidden);
            kegiatanEvaluasi.disabled = !sumber || pilihanTersedia.length === 0;
            pesanKegiatanKosong.classList.toggle('hidden', sumber !== 'internal' || pilihanTersedia.length > 0);

            if (!pilihanTersedia.some((pilihan) => pilihan.value === nilaiLama)) {
                kegiatanEvaluasi.value = '';
            }

            kegiatanEvaluasi.options[0].textContent = !sumber
                ? 'Pilih Sumber Terlebih Dahulu'
                : pilihanTersedia.length
                    ? 'Pilih Kegiatan'
                    : 'Belum ada kegiatan';
        }

        function bukaTambah() {
            modalEvaluasi.classList.remove('hidden');
        }

        function tutupModal() {
            modalEvaluasi.classList.add('hidden');
        }

        function bukaEditEvaluasi(evaluation) {
            formEditEvaluasi.action = polaUrlEditEvaluasi.replace('__ACTIVITY__', evaluation.id);
            document.getElementById('evaluation-context-id').value = evaluation.id;
            document.getElementById('edit-evaluation-name').textContent = evaluation.name;
            document.getElementById('edit-evaluation-notes').value = evaluation.notes ?? '';
            document.getElementById('edit-evaluation-obstacles').value = evaluation.obstacles ?? '';
            modalEditEvaluasi.classList.remove('hidden');
        }

        function tutupEditEvaluasi() {
            modalEditEvaluasi.classList.add('hidden');
        }

        if (kegiatanEvaluasi.value) {
            sumberEvaluasi.value = kegiatanEvaluasi.selectedOptions[0]?.dataset.source ?? '';
            kategoriEvaluasi.value = kegiatanEvaluasi.selectedOptions[0]?.dataset.category || '__uncategorized__';
        }

        filterKegiatan();

        @if ($editingEvaluation)
            bukaEditEvaluasi(@js([
                'id' => $editingEvaluation->id,
                'name' => $editingEvaluation->name,
                'notes' => old('evaluation_notes', $editingEvaluation->evaluation_notes),
                'obstacles' => old('evaluation_obstacles', $editingEvaluation->evaluation_obstacles),
            ]));
        @endif
    </script>
@endpush
