@extends($activityLayout ?? 'layouts.admin')

@section('title', 'Acara & Kegiatan')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="font-headline text-3xl font-bold tracking-tight text-slate-900">Acara &amp; Kegiatan</h1>
                <p class="mt-1 text-sm text-slate-500">Data kegiatan operasional dari database, termasuk tindak lanjut laporan masyarakat.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <button id="export-excel" type="button" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-70">
                    <span class="material-symbols-outlined text-[18px]">table_view</span>
                    Ekspor Excel
                </button>
                <button id="export-pdf" type="button" class="flex items-center gap-2 rounded-xl bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 disabled:cursor-wait disabled:opacity-70">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    Ekspor PDF
                </button>
                <a href="{{ route(($activityRoutePrefix ?? 'admin.kegiatan').'.create') }}" class="flex items-center gap-2 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-colors hover:bg-sidebar-hover">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Kegiatan
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route($activityRoutePrefix ?? 'admin.kegiatan') }}" method="GET" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="activity-search" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Pencarian</label>
                    <input id="activity-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, laporan, lokasi" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm">
                </div>
                <div>
                    <label for="month" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Bulan kegiatan</label>
                    <input id="month" name="month" type="month" value="{{ $filters['month'] ?? '' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
                </div>
                <div>
                    <label for="date_start" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Mulai tanggal</label>
                    <input id="date_start" name="date_start" type="date" value="{{ $filters['date_start'] ?? '' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
                </div>
                <div>
                    <label for="date_end" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Sampai tanggal</label>
                    <input id="date_end" name="date_end" type="date" value="{{ $filters['date_end'] ?? '' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
                </div>
                <div>
                    <label for="type" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis kegiatan</label>
                    <select id="type" name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-sidebar-active focus:bg-white focus:ring-2 focus:ring-sidebar-active/20">
                        <option value="">Semua jenis</option>
                        @foreach ($activityTypes as $activityType)
                            <option value="{{ $activityType->value }}" @selected(($filters['type'] ?? '') === $activityType->value)>{{ $activityType->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</label>
                    <select id="category" name="category" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-sidebar-active px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-sidebar-hover">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                        Terapkan
                    </button>
                    <a href="{{ route($activityRoutePrefix ?? 'admin.kegiatan') }}" title="Reset filter" class="flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-600 transition-colors hover:bg-slate-50">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    </a>
                </div>
            </div>
        </form>

        <div class="flex flex-col gap-2 px-1 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
            <p>
                Menampilkan <strong class="text-slate-800">{{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }}</strong>
                dari <strong class="text-slate-800">{{ $activities->total() }}</strong> kegiatan
            </p>
            @if (array_filter($filters, fn ($value) => $value !== null && $value !== ''))
                <span class="inline-flex self-start items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                    <span class="material-symbols-outlined text-[14px]">filter_alt</span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nama Kegiatan</th>
                            <th class="px-6 py-4 font-bold">Tanggal</th>
                            <th class="px-6 py-4 font-bold">Jenis</th>
                            <th class="px-6 py-4 font-bold">Kategori</th>
                            <th class="px-6 py-4 font-bold">Jenis Temuan</th>
                            <th class="px-6 py-4 text-center font-bold">Jumlah Temuan</th>
                            <th class="px-6 py-4 font-bold">Satuan Temuan</th>
                            <th class="px-6 py-4 font-bold">Lokasi</th>
                            <th class="px-6 py-4 text-center font-bold">Petugas</th>
                            <th class="px-6 py-4 text-center font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activities as $activity)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex min-w-[220px] flex-col gap-1.5">
                                        <span class="font-semibold text-slate-800">{{ $activity->name }}</span>
                                        @if ($activity->report !== null)
                                            <span class="inline-flex w-fit items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600">
                                                <span class="material-symbols-outlined text-[12px]">link</span>
                                                Dari Laporan {{ $activity->report->code }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-slate-500">
                                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                                        <span>
                                            {{ $activity->activity_date->format('d/m/Y') }}
                                            @if ($activity->start_time !== null)
                                                · {{ $activity->start_time->format('H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        'border-orange-100 bg-orange-50 text-orange-600' => $activity->type === \App\ActivityType::External,
                                        'border-indigo-100 bg-indigo-50 text-indigo-600' => $activity->type === \App\ActivityType::Internal,
                                    ])>{{ $activity->type->label() }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="line-clamp-2 min-w-[160px]">{{ $activity->category ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 align-top"><div class="space-y-1">@forelse ($activity->findings as $finding)<div class="whitespace-nowrap">{{ $finding->type->label() }}</div>@empty - @endforelse</div></td>
                                <td class="px-6 py-4 text-center align-top"><div class="space-y-1">@forelse ($activity->findings as $finding)<div>{{ $finding->quantity }}</div>@empty - @endforelse</div></td>
                                <td class="px-6 py-4 align-top"><div class="space-y-1">@forelse ($activity->findings as $finding)<div class="whitespace-nowrap">{{ $finding->unit->label() }}</div>@empty - @endforelse</div></td>
                                <td class="max-w-[240px] px-6 py-4">
                                    <span class="line-clamp-2" title="{{ $activity->location_address }}">{{ $activity->location_address }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="whitespace-nowrap rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">{{ $activity->officers_count }} orang</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        @if ($activity->report !== null && ($activityRoutePrefix ?? 'admin.kegiatan') === 'admin.kegiatan')
                                            <a href="{{ route('admin.pengaduan.detail', $activity->report) }}" title="Lihat laporan dan kegiatan" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </a>
                                        @elseif ($activity->report !== null)
                                            <a href="{{ route('super_admin.pengaduan.detail', $activity->report) }}" title="Lihat laporan dan kegiatan" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </a>
                                        @else
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route(($activityRoutePrefix ?? 'admin.kegiatan').'.show', $activity) }}" title="Lihat detail kegiatan" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600">
                                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                                </a>
                                                <a href="{{ route(($activityRoutePrefix ?? 'admin.kegiatan').'.edit', $activity) }}" title="Edit kegiatan" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-amber-50 hover:text-amber-600">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </a>
                                                <form action="{{ route(($activityRoutePrefix ?? 'admin.kegiatan').'.destroy', $activity) }}" method="POST" data-confirm-delete="Hapus kegiatan ini? Data akan disembunyikan dari daftar, tetapi tetap tersimpan sebagai arsip.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Hapus kegiatan" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-14 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                            <span class="material-symbols-outlined text-3xl">calendar_month</span>
                                        </div>
                                        <h2 class="mt-4 text-lg font-bold text-slate-800">Tidak ada kegiatan</h2>
                                        <p class="mt-1 text-sm text-slate-500">Belum ada data atau tidak ada kegiatan yang sesuai dengan filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($activities->hasPages())
            <div>{{ $activities->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const activities = {{ Illuminate\Support\Js::from($exportActivities) }};
            const filters = {{ Illuminate\Support\Js::from($filters) }};
            const excelButton = document.getElementById('export-excel');
            const pdfButton = document.getElementById('export-pdf');

            const loadScript = (source) => new Promise((resolve, reject) => {
                if (document.querySelector(`script[src="${source}"]`)) return resolve();
                const script = document.createElement('script');
                script.src = source;
                script.onload = resolve;
                script.onerror = () => reject(new Error('Pustaka ekspor gagal dimuat. Periksa koneksi internet.'));
                document.head.appendChild(script);
            });
            const dateLabel = (date) => new Date(`${date}T00:00:00`).toLocaleDateString('id-ID', {
                weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
            }).toUpperCase();
            const period = () => {
                const dates = activities.map(activity => activity.tanggal).sort();
                if (filters.date_start || filters.date_end) {
                    return [filters.date_start || dates[0], filters.date_end || dates[dates.length - 1]];
                }
                if (filters.month) {
                    const [year, month] = filters.month.split('-').map(Number);
                    return [`${filters.month}-01`, `${filters.month}-${String(new Date(year, month, 0).getDate()).padStart(2, '0')}`];
                }
                return [dates[0], dates[dates.length - 1]];
            };
            const suffix = () => {
                const [start, end] = period();
                return `${start}_sd_${end}`;
            };
            const ensureData = () => {
                if (activities.length) return true;
                window.tampilToast('Tidak ada kegiatan yang sesuai dengan filter untuk diekspor.', 'warning');
                return false;
            };
            const imageData = async (source) => {
                try {
                    const response = await fetch(source, { credentials: 'same-origin' });
                    if (!response.ok) return null;
                    const url = URL.createObjectURL(await response.blob());
                    return await new Promise(resolve => {
                        const image = new Image();
                        image.onload = () => {
                            const canvas = document.createElement('canvas');
                            canvas.width = image.naturalWidth;
                            canvas.height = image.naturalHeight;
                            canvas.getContext('2d').drawImage(image, 0, 0);
                            URL.revokeObjectURL(url);
                            resolve({
                                data: canvas.toDataURL('image/jpeg', 0.82),
                                width: image.naturalWidth,
                                height: image.naturalHeight,
                            });
                        };
                        image.onerror = () => { URL.revokeObjectURL(url); resolve(null); };
                        image.src = url;
                    });
                } catch (_) {
                    return null;
                }
            };
            const pdfImageSize = (image, cellWidth) => {
                const maximumWidth = Math.min(52, cellWidth - 6);
                const maximumHeight = 30;
                const scale = Math.min(maximumWidth / image.width, maximumHeight / image.height);

                return {
                    width: image.width * scale,
                    height: image.height * scale,
                };
            };

            excelButton.addEventListener('click', async () => {
                if (!ensureData()) return;
                excelButton.disabled = true;
                try {
                    if (typeof XLSX === 'undefined') await loadScript('https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js');
                    const rows = activities.map((activity, index) => ({
                        'Nomor': index + 1, 'ID Kegiatan': activity.id, 'ID Laporan': activity.id_laporan || '-',
                        'Kode Laporan': activity.kode_laporan || '-', 'ID Template': activity.id_template || '-',
                        'Nama Template': activity.nama_template || '-', 'Dibuat Oleh': activity.dibuat_oleh || '-',
                        'Dievaluasi Oleh': activity.dievaluasi_oleh || '-', 'Nama Kegiatan': activity.nama,
                        'Jenis': activity.jenis, 'Kategori': activity.kategori || '-', 'Tanggal': activity.tanggal,
                        'Jenis Temuan': activity.temuan.map(item => item.jenis).join(', ') || '-',
                        'Jumlah Temuan': activity.temuan.map(item => item.jumlah).join(', ') || '-',
                        'Satuan Temuan': activity.temuan.map(item => item.satuan).join(', ') || '-',
                        'Waktu': activity.waktu ? `${activity.waktu} WIB` : '-', 'Deskripsi/Tindakan': activity.deskripsi || '-',
                        'Hasil': activity.hasil || '-', 'Lokasi': activity.lokasi, 'Lintang': activity.lintang,
                        'Bujur': activity.bujur, 'Jumlah Personel': activity.personel.length,
                        'Daftar Personel': activity.personel.join(', ') || '-',
                        'Dokumentasi': activity.dokumentasi.map(item => item.nama).join(', ') || '-',
                        'Catatan Evaluasi': activity.catatan_evaluasi || '-', 'Kendala Evaluasi': activity.kendala_evaluasi || '-',
                        'Waktu Evaluasi': activity.waktu_evaluasi || '-', 'Dibuat Pada': activity.dibuat_pada,
                        'Diperbarui Pada': activity.diperbarui_pada,
                    }));
                    const sheet = XLSX.utils.json_to_sheet(rows);
                    sheet['!cols'] = Object.keys(rows[0]).map(header => ({ wch: Math.min(55, Math.max(12, header.length + 2)) }));
                    const book = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(book, sheet, 'Data Kegiatan');
                    XLSX.writeFile(book, `Laporan_Kegiatan_${suffix()}.xlsx`);
                } catch (error) {
                    window.tampilToast(error.message, 'error');
                } finally {
                    excelButton.disabled = false;
                }
            });

            pdfButton.addEventListener('click', async () => {
                if (!ensureData()) return;
                pdfButton.disabled = true;
                try {
                    if (!window.jspdf?.jsPDF) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
                    if (!window.jspdf.jsPDF.prototype.autoTable) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js');
                    const { jsPDF } = window.jspdf;
                    const documentPdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                    const [start, end] = period();
                    documentPdf.setFont('helvetica', 'bold');
                    documentPdf.setFontSize(14);
                    ['LAPORAN KEGIATAN', 'SATUAN POLISI PAMONG PRAJA', 'KABUPATEN KULON PROGO'].forEach((line, index) =>
                        documentPdf.text(line, 148.5, 13 + (index * 6), { align: 'center' }));
                    documentPdf.setFontSize(11);
                    documentPdf.text(`${dateLabel(start)}-${dateLabel(end)}`, 148.5, 33, { align: 'center' });
                    const images = [];
                    const body = await Promise.all(activities.map(async (activity, index) => {
                        images[index] = (await Promise.all(activity.dokumentasi.map(item => imageData(item.url)))).filter(Boolean);
                        return [index + 1, `${activity.tanggal}\n${activity.waktu ? activity.waktu + ' WIB' : '-'}`,
                            activity.personel.join(', ') || '-',
                            `Kegiatan: ${activity.nama}\nLokasi: ${activity.lokasi}\nTindakan: ${activity.deskripsi || '-'}\nHasil: ${activity.hasil || '-'}\nTemuan: ${activity.temuan.map(item => `${item.jenis}: ${item.jumlah} ${item.satuan}`).join(', ') || '-'}`,
                            images[index].length ? '\n' : '-'];
                    }));
                    documentPdf.autoTable({
                        head: [['Nomor', 'Tanggal/Waktu', 'Personel', 'Tindakan dan Hasil', 'Dokumentasi']], body,
                        startY: 39, theme: 'grid', rowPageBreak: 'avoid', margin: { left: 14, right: 14 },
                        headStyles: { fillColor: [43, 79, 124], halign: 'center', fontStyle: 'bold' },
                        bodyStyles: { fontSize: 8, cellPadding: 3 },
                        columnStyles: { 0: { cellWidth: 12, halign: 'center' }, 1: { cellWidth: 30, halign: 'center' }, 2: { cellWidth: 48 }, 3: { cellWidth: 116 }, 4: { cellWidth: 62, halign: 'center' } },
                        didParseCell: data => {
                            if (data.section !== 'body' || data.column.index !== 4 || !images[data.row.index]?.length) return;

                            data.cell.styles.minCellHeight = images[data.row.index].reduce((height, image, index) => (
                                height + pdfImageSize(image, data.cell.width).height + (index > 0 ? 4 : 0)
                            ), 6);
                        },
                        didDrawCell: data => {
                            if (data.section !== 'body' || data.column.index !== 4) return;

                            let imageY = data.cell.y + 3;
                            (images[data.row.index] || []).forEach(image => {
                                const size = pdfImageSize(image, data.cell.width);
                                documentPdf.addImage(image.data, 'JPEG', data.cell.x + ((data.cell.width - size.width) / 2), imageY, size.width, size.height);
                                imageY += size.height + 4;
                            });
                        },
                    });

                    const exportDate = new Date().toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'long', year: 'numeric'
                    });
                    const signatureLines = [
                        `Kulon Progo, ${exportDate}`,
                        'Menyetujui,',
                        'Kepala Satuan Polisi Pamong Praja',
                        'Kabupaten Kulon Progo',
                    ];
                    const signatureNameLines = [
                        'BUDI HARTONO, S.Si, M.Si',
                        'Pembina Utama Muda, IV/c',
                        'NIP. 19690318 199603 1 003',
                    ];
                    let signatureY = (documentPdf.lastAutoTable.finalY || 39) + 12;
                    const pageHeight = documentPdf.internal.pageSize.getHeight();
                    if (signatureY + 58 > pageHeight - 10) {
                        documentPdf.addPage();
                        signatureY = 20;
                    }
                    const signatureX = documentPdf.internal.pageSize.getWidth() - 67;
                    documentPdf.setFont('helvetica', 'normal');
                    documentPdf.setFontSize(10);
                    signatureLines.forEach((line, index) => documentPdf.text(line, signatureX, signatureY + (index * 5), { align: 'center' }));
                    documentPdf.setFont('helvetica', 'bold');
                    documentPdf.text(signatureNameLines[0], signatureX, signatureY + 37, { align: 'center' });
                    documentPdf.setFont('helvetica', 'normal');
                    documentPdf.text(signatureNameLines[1], signatureX, signatureY + 42, { align: 'center' });
                    documentPdf.text(signatureNameLines[2], signatureX, signatureY + 47, { align: 'center' });
                    documentPdf.save(`Laporan_Kegiatan_${suffix()}.pdf`);
                } catch (error) {
                    window.tampilToast(error.message, 'error');
                } finally {
                    pdfButton.disabled = false;
                }
            });
        })();
    </script>
@endpush
