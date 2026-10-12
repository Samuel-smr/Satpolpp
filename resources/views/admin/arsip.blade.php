@extends('layouts.admin')

@section('title', 'Arsip & Dokumen')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Arsip & Dokumen</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola dokumen operasional dan media pendukung Satpol PP.</p>
        </div>
        <button type="button" onclick="bukaTambahArsip()"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidebar-active px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-sidebar-hover">
            <span class="material-symbols-outlined text-[20px]">upload_file</span>
            Unggah Arsip
        </button>
    </div>

    <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Arsip', 'value' => $archiveSummary['total'], 'icon' => 'folder', 'class' => 'bg-sidebar-active/10 text-sidebar-active'],
            ['label' => 'Dokumen', 'value' => $archiveSummary['documents'], 'icon' => 'description', 'class' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Media', 'value' => $archiveSummary['media'], 'icon' => 'image', 'class' => 'bg-emerald-50 text-emerald-600'],
            ['label' => 'Rahasia', 'value' => $archiveSummary['confidential'], 'icon' => 'lock', 'class' => 'bg-amber-50 text-amber-600'],
        ] as $stat)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ $stat['label'] }}</p>
                        <p class="font-headline mt-1 text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat['class'] }}">
                        <span class="material-symbols-outlined">{{ $stat['icon'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.arsip') }}" class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_240px_auto]">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100"
                    placeholder="Cari nama, file, atau deskripsi arsip..."
                    class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-3 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
            </div>
            <select name="category"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                <option value="">Semua Kategori</option>
                @foreach ($activityTemplates as $activityTemplate)
                    <option value="{{ $activityTemplate->name }}" @selected(($filters['category'] ?? '') === $activityTemplate->name)>{{ $activityTemplate->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-sidebar-active px-5 py-2.5 font-semibold text-white hover:bg-sidebar-hover">Filter</button>
                <a href="{{ route('admin.arsip') }}" title="Reset filter"
                    class="flex items-center justify-center rounded-xl border border-slate-300 px-3 text-slate-600 hover:bg-slate-50">
                    <span class="material-symbols-outlined">restart_alt</span>
                </a>
            </div>
        </div>
        @error('search')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        @error('category')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="text-sm font-semibold text-slate-700">Daftar Arsip</span>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Data Database</span>
        </div>

        @if ($archives->isEmpty())
            <div class="px-6 py-14 text-center text-slate-400">
                <span class="material-symbols-outlined mb-2 text-5xl">folder_off</span>
                <p class="font-medium">Belum ada arsip yang sesuai.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($archives as $archive)
                    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <span class="material-symbols-outlined text-[26px]">folder</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h2 class="truncate font-semibold text-slate-800" title="{{ $archive->title }}">{{ $archive->title }}</h2>
                                    @if ($archive->confidentiality === \App\ArchiveConfidentiality::Confidential)
                                        <span class="material-symbols-outlined text-[18px] text-amber-500" title="Dokumen rahasia">lock</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $archive->files->count() }} file</p>
                            </div>
                        </div>

                        <p class="mt-4 line-clamp-2 min-h-10 text-sm leading-relaxed text-slate-500">{{ $archive->description ?: 'Tidak ada deskripsi.' }}</p>
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-4 text-xs text-slate-500">
                            <p><span class="font-semibold text-slate-600">Kategori:</span> {{ $archive->operational_category }}</p>
                            <p><span class="font-semibold text-slate-600">Ukuran:</span> {{ number_format($archive->files->sum('file_size') / 1048576, 2, ',', '.') }} MB</p>
                            <p><span class="font-semibold text-slate-600">Diunggah:</span> {{ $archive->created_at->format('d/m/Y H:i') }} oleh {{ $archive->createdByUser?->name ?? '-' }}</p>
                        </div>

                        <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                            @foreach ($archive->files as $archiveFile)
                                <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs">
                                    <span class="material-symbols-outlined text-[18px] text-slate-400">description</span>
                                    <span class="min-w-0 flex-1 truncate" title="{{ $archiveFile->original_name }}">{{ $archiveFile->original_name }}</span>
                                    <a href="{{ route('admin.arsip.show', [$archive, $archiveFile]) }}" target="_blank" rel="noopener" class="text-blue-600" title="Lihat file"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('admin.arsip.download', [$archive, $archiveFile]) }}" class="text-emerald-600" title="Unduh file"><span class="material-symbols-outlined text-[18px]">download</span></a>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-1 border-t border-slate-100 pt-3">
                            <button type="button"
                                onclick="bukaEditArsip(@js([
                                    'id' => $archive->id,
                                    'title' => $archive->title,
                                    'operational_category' => $archive->operational_category,
                                    'description' => $archive->description,
                                    'confidentiality' => $archive->confidentiality->value,
                                    'file_count' => $archive->files->count(),
                                    'file_names' => $archive->files->pluck('original_name')->all(),
                                ]))"
                                class="rounded-lg p-2 text-slate-400 hover:bg-amber-50 hover:text-amber-600" title="Edit arsip">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <form method="POST" action="{{ route('admin.arsip.destroy', $archive) }}"
                                data-confirm-delete="Hapus arsip &quot;{{ $archive->title }}&quot;? Arsip akan disembunyikan, tetapi file dan datanya tetap tersimpan agar dapat dipulihkan.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Hapus arsip">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($archives->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $archives->links() }}</div>
            @endif
        @endif
    </div>

    <div id="archive-modal" @class(['fixed inset-0 z-[100] flex items-center justify-center p-4', 'hidden' => !$errors->any()])>
        <div class="absolute inset-0 bg-black/40" onclick="tutupModalArsip()"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 id="archive-modal-title" class="font-headline text-lg font-bold text-slate-800">Unggah Arsip Baru</h2>
                    <p id="archive-modal-description" class="mt-0.5 text-sm text-slate-500">Lengkapi form untuk mengunggah dokumen arsip.</p>
                </div>
                <button type="button" onclick="tutupModalArsip()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="archive-form" method="POST" action="{{ route('admin.arsip.store') }}" enctype="multipart/form-data" class="flex-1 space-y-5 overflow-y-auto p-6">
                @csrf
                <input id="archive-method" type="hidden" name="_method" value="POST">
                <input id="archive-context-id" type="hidden" name="archive_context_id" value="{{ old('archive_context_id') }}">

                <div>
                    <label for="archive-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Arsip / Dokumen <span class="text-red-500">*</span></label>
                    <input id="archive-title" type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                        placeholder="Contoh: BAP Penertiban PKL"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="archive-category" class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori <span class="text-red-500">*</span></label>
                        <select id="archive-category" name="operational_category" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                            <option value="">Pilih Kategori</option>
                            @foreach ($activityTemplates as $activityTemplate)
                                <option value="{{ $activityTemplate->name }}" @selected(old('operational_category') === $activityTemplate->name)>{{ $activityTemplate->name }}</option>
                            @endforeach
                        </select>
                        @error('operational_category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="archive-confidentiality" class="mb-1.5 block text-sm font-semibold text-slate-700">Sifat Dokumen <span class="text-red-500">*</span></label>
                        <select id="archive-confidentiality" name="confidentiality" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">
                            @foreach ($confidentialities as $confidentiality)
                                <option value="{{ $confidentiality->value }}" @selected(old('confidentiality', \App\ArchiveConfidentiality::Public->value) === $confidentiality->value)>{{ $confidentiality->label() }}</option>
                            @endforeach
                        </select>
                        @error('confidentiality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="archive-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Keterangan / Deskripsi Ringkas</label>
                    <textarea id="archive-description" name="description" rows="3" maxlength="5000"
                        placeholder="Tuliskan keterangan mengenai isi dokumen..."
                        class="w-full resize-y rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-sidebar-active focus:ring-1 focus:ring-sidebar-active">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="archive-file" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Unggah File <span id="archive-file-required" class="text-red-500">*</span>
                    </label>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition-colors hover:bg-slate-100">
                        <span class="material-symbols-outlined mb-2 text-4xl text-slate-400">cloud_upload</span>
                        <p id="archive-file-name" class="text-sm font-medium text-slate-700">Pilih maksimal 5 file arsip</p>
                        <p class="mt-1 text-xs text-slate-500">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, atau ZIP. Maksimal 5 file, masing-masing 20 MB.</p>
                        <input id="archive-file" type="file" name="files[]" required multiple
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip" class="sr-only"
                            onchange="tampilkanNamaFile(this)">
                    </label>
                    <div id="archive-file-preview" class="mt-3 hidden grid gap-3 sm:grid-cols-3"></div>
                    <p id="archive-current-file" class="mt-2 hidden text-xs text-amber-600"></p>
                    @error('files')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('files.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" onclick="tutupModalArsip()" class="rounded-xl border border-slate-300 px-5 py-2.5 font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-sidebar-active px-6 py-2.5 font-semibold text-white hover:bg-sidebar-hover">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span id="archive-submit-label">Simpan & Unggah</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalArsip = document.getElementById('archive-modal');
        const formArsip = document.getElementById('archive-form');
        const archiveFilePreview = document.getElementById('archive-file-preview');
        const urlTambahArsip = @json(route('admin.arsip.store'));
        const polaUrlEditArsip = @json(route('admin.arsip.update', ['archive' => '__ARCHIVE__']));
        let archivePreviewUrls = [];

        function bersihkanPreviewArsip() {
            archivePreviewUrls.forEach((url) => URL.revokeObjectURL(url));
            archivePreviewUrls = [];
            archiveFilePreview.replaceChildren();
            archiveFilePreview.classList.add('hidden');
        }

        function bukaTambahArsip() {
            formArsip.reset();
            bersihkanPreviewArsip();
            formArsip.action = urlTambahArsip;
            document.getElementById('archive-method').value = 'POST';
            document.getElementById('archive-context-id').value = '';
            document.getElementById('archive-modal-title').textContent = 'Unggah Arsip Baru';
            document.getElementById('archive-modal-description').textContent = 'Lengkapi form untuk mengunggah dokumen arsip.';
            document.getElementById('archive-submit-label').textContent = 'Simpan & Unggah';
            document.getElementById('archive-file').required = true;
            document.getElementById('archive-file').multiple = true;
            document.getElementById('archive-file').dataset.maximumFiles = '5';
            document.getElementById('archive-file-required').classList.remove('hidden');
            document.getElementById('archive-current-file').classList.add('hidden');
            document.getElementById('archive-file-name').textContent = 'Pilih maksimal 5 file arsip';
            modalArsip.classList.remove('hidden');
        }

        function bukaEditArsip(archive) {
            formArsip.reset();
            bersihkanPreviewArsip();
            formArsip.action = polaUrlEditArsip.replace('__ARCHIVE__', archive.id);
            document.getElementById('archive-method').value = 'PUT';
            document.getElementById('archive-context-id').value = archive.id;
            formArsip.elements.title.value = archive.title;
            formArsip.elements.operational_category.value = archive.operational_category;
            formArsip.elements.description.value = archive.description ?? '';
            formArsip.elements.confidentiality.value = archive.confidentiality;
            document.getElementById('archive-modal-title').textContent = 'Edit Arsip / Dokumen';
            document.getElementById('archive-modal-description').textContent = 'Ubah metadata atau unggah file pengganti.';
            document.getElementById('archive-submit-label').textContent = 'Perbarui Arsip';
            document.getElementById('archive-file').required = false;
            document.getElementById('archive-file').multiple = true;
            document.getElementById('archive-file').dataset.maximumFiles = String(5 - archive.file_count);
            document.getElementById('archive-file-required').classList.add('hidden');
            const currentFile = document.getElementById('archive-current-file');
            currentFile.textContent = `File saat ini: ${archive.file_names.join(', ')}. Anda dapat menambahkan sampai ${5 - archive.file_count} file lagi.`;
            currentFile.classList.remove('hidden');
            document.getElementById('archive-file-name').textContent = 'Pilih file pengganti (opsional)';
            modalArsip.classList.remove('hidden');
        }

        function tutupModalArsip() {
            modalArsip.classList.add('hidden');
        }

        function tampilkanNamaFile(input) {
            bersihkanPreviewArsip();

            const maximumFiles = Number.parseInt(input.dataset.maximumFiles || '5', 10);
            if (input.files.length > maximumFiles) {
                input.value = '';
                document.getElementById('archive-file-name').textContent = 'Maksimal 5 file arsip';
                window.tampilToast?.(`Anda hanya dapat menambahkan ${maximumFiles} file lagi.`, 'warning');

                return;
            }

            const fileNames = Array.from(input.files).map((file) => file.name);
            document.getElementById('archive-file-name').textContent = fileNames.length > 0
                ? `${fileNames.length} file dipilih: ${fileNames.join(', ')}`
                : (input.multiple ? 'Pilih maksimal 5 file arsip' : 'Pilih file pengganti (opsional)');

            Array.from(input.files).forEach((file, index) => {
                const card = document.createElement('div');
                card.className = 'relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50';

                if (file.type.startsWith('image/')) {
                    const previewUrl = URL.createObjectURL(file);
                    archivePreviewUrls.push(previewUrl);

                    const image = document.createElement('img');
                    image.src = previewUrl;
                    image.alt = `Preview ${file.name}`;
                    image.className = 'aspect-video w-full object-cover';
                    card.appendChild(image);
                } else {
                    const icon = document.createElement('div');
                    icon.className = 'flex aspect-video items-center justify-center bg-slate-100 text-slate-400';
                    icon.innerHTML = '<span class="material-symbols-outlined text-4xl">description</span>';
                    card.appendChild(icon);
                }

                const name = document.createElement('p');
                name.className = 'truncate px-3 py-2 text-xs font-medium text-slate-600';
                name.textContent = file.name;
                card.appendChild(name);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.title = 'Hapus file terpilih';
                removeButton.className = 'absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-red-600 shadow transition-colors hover:bg-red-50';
                removeButton.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
                removeButton.addEventListener('click', () => {
                    const remainingFiles = new DataTransfer();
                    Array.from(input.files).forEach((selectedFile, selectedIndex) => {
                        if (selectedIndex !== index) {
                            remainingFiles.items.add(selectedFile);
                        }
                    });
                    input.files = remainingFiles.files;
                    tampilkanNamaFile(input);
                });
                card.appendChild(removeButton);
                archiveFilePreview.appendChild(card);
            });

            archiveFilePreview.classList.toggle('hidden', input.files.length === 0);
        }

        @if ($errors->any() && $editingArchive)
            bukaEditArsip(@js([
                'id' => $editingArchive->id,
                'title' => old('title', $editingArchive->title),
                'operational_category' => old('operational_category', $editingArchive->operational_category),
                'description' => old('description', $editingArchive->description),
                'confidentiality' => old('confidentiality', $editingArchive->confidentiality->value),
                'file_count' => $editingArchive->files()->count(),
                'file_names' => $editingArchive->files()->pluck('original_name')->all(),
            ]));
        @elseif ($errors->any())
            modalArsip.classList.remove('hidden');
        @endif
    </script>
@endpush
