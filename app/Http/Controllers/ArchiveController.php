<?php

namespace App\Http\Controllers;

use App\ArchiveConfidentiality;
use App\Http\Requests\UpsertArchiveRequest;
use App\Models\ActivityTemplate;
use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ArchiveController extends Controller
{
    /** @var list<string> */
    private const MEDIA_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $archives = Archive::query()
            ->with(['createdByUser:id,name', 'files' => fn ($query) => $query->oldest('id')])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('title', 'like', "%{$search}%")
                        ->orWhereHas('files', fn ($fileQuery) => $fileQuery->where('original_name', 'like', "%{$search}%"))
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('operational_category', $category))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = Archive::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN confidentiality = ? THEN 1 ELSE 0 END) as confidential', [ArchiveConfidentiality::Confidential->value])
            ->first();
        $mediaCount = Archive::query()->whereHas('files', fn ($query) => $query->whereIn('file_extension', self::MEDIA_EXTENSIONS))->count();

        $editingArchiveId = (int) $request->old('archive_context_id', 0);
        $editingArchive = $editingArchiveId > 0
            ? Archive::query()->with('files')->find($editingArchiveId)
            : null;

        return view('admin.arsip', [
            'archives' => $archives,
            'filters' => $filters,
            'activityTemplates' => ActivityTemplate::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'confidentialities' => ArchiveConfidentiality::cases(),
            'archiveSummary' => [
                'total' => (int) $summary->total,
                'documents' => (int) $summary->total - $mediaCount,
                'media' => $mediaCount,
                'confidential' => (int) $summary->confidential,
            ],
            'editingArchive' => $editingArchive,
        ]);
    }

    public function store(UpsertArchiveRequest $request): RedirectResponse
    {
        /** @var list<UploadedFile> $files */
        $files = $request->file('files', []);
        $storedFiles = [];

        try {
            foreach ($files as $file) {
                $path = $file->store('archives/files', 'local');

                if ($path === false) {
                    throw new RuntimeException('File arsip gagal disimpan.');
                }

                $storedFiles[] = ['file' => $file, 'path' => $path];
            }

            DB::transaction(function () use ($request, $storedFiles): void {
                $archive = Archive::create([
                    ...$request->safe()->only(['title', 'operational_category', 'description', 'confidentiality']),
                    'created_by_user_id' => $request->user()->id,
                ]);

                foreach ($storedFiles as $storedFile) {
                    $archive->files()->create($this->fileAttributes($storedFile['file'], $storedFile['path']));
                }

                $this->writeAuditLog($request, $archive, 'created', "Arsip {$archive->title} diunggah.");
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedFiles, 'path'));

            throw $exception;
        }

        return redirect()->route('admin.arsip')->with('status', 'Arsip berhasil diunggah.');
    }

    public function update(UpsertArchiveRequest $request, Archive $archive): RedirectResponse
    {
        /** @var list<UploadedFile> $files */
        $files = $request->file('files', []);
        $storedFiles = [];

        try {
            foreach ($files as $file) {
                $path = $file->store('archives/files', 'local');
                if ($path === false) {
                    throw new RuntimeException('File arsip gagal disimpan.');
                }
                $storedFiles[] = ['file' => $file, 'path' => $path];
            }

            DB::transaction(function () use ($request, $archive, $storedFiles): void {
                $archive->update($request->safe()->only(['title', 'operational_category', 'description', 'confidentiality']));
                foreach ($storedFiles as $storedFile) {
                    $archive->files()->create($this->fileAttributes($storedFile['file'], $storedFile['path']));
                }
                $this->writeAuditLog($request, $archive, 'updated', "Arsip {$archive->title} diperbarui.");
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedFiles, 'path'));

            throw $exception;
        }

        return redirect()->route('admin.arsip')->with('status', 'Arsip berhasil diperbarui.');
    }

    public function show(Archive $archive, ArchiveFile $archiveFile): StreamedResponse
    {
        Gate::authorize('access-admin');
        $this->ensureArchiveFile($archive, $archiveFile);

        $disposition = in_array($archiveFile->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true)
            ? 'inline'
            : 'attachment';

        return Storage::disk('local')->response(
            $archiveFile->file_path,
            $archiveFile->original_name,
            $this->secureHeaders($archiveFile),
            $disposition,
        );
    }

    public function download(Archive $archive, ArchiveFile $archiveFile): StreamedResponse
    {
        Gate::authorize('access-admin');
        $this->ensureArchiveFile($archive, $archiveFile);

        return Storage::disk('local')->download(
            $archiveFile->file_path,
            $archiveFile->original_name,
            $this->secureHeaders($archiveFile),
        );
    }

    public function destroy(Request $request, Archive $archive): RedirectResponse
    {
        Gate::authorize('access-admin');
        $archive->delete();
        $this->writeAuditLog($request, $archive, 'deleted', "Arsip {$archive->title} dihapus.");

        return redirect()->route('admin.arsip')->with('status', 'Arsip berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function fileAttributes(UploadedFile $file, string $path): array
    {
        return [
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_extension' => strtolower($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
        ];
    }

    /** @return array<string, string> */
    private function secureHeaders(ArchiveFile $archiveFile): array
    {
        return [
            'Content-Type' => $archiveFile->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'private, no-store',
        ];
    }

    private function ensureArchiveFile(Archive $archive, ArchiveFile $archiveFile): void
    {
        if ($archiveFile->archive_id !== $archive->id || ! Storage::disk('local')->exists($archiveFile->file_path)) {
            throw new NotFoundHttpException;
        }
    }

    private function writeAuditLog(Request $request, Archive $archive, string $action, string $description): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'subject_type' => 'archive',
            'subject_id' => $archive->id,
            'module' => 'archives',
            'action' => $action,
            'description' => $description,
            'old_values' => null,
            'new_values' => ['file_count' => $archive->files()->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
