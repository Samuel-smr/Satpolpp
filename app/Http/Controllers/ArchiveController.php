<?php

namespace App\Http\Controllers;

use App\ArchiveConfidentiality;
use App\Http\Requests\UpsertArchiveRequest;
use App\Models\Archive;
use App\Models\AuditLog;
use App\OperationalCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
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
            'category' => ['nullable', Rule::enum(OperationalCategory::class)],
            'type' => ['nullable', Rule::in(['document', 'media'])],
        ], [
            'category.enum' => 'Kategori arsip tidak valid.',
            'type.in' => 'Jenis arsip tidak valid.',
        ]);

        $archives = Archive::query()
            ->with('createdByUser:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('operational_category', $category))
            ->when(($filters['type'] ?? null) === 'media', fn ($query) => $query->whereIn('file_extension', self::MEDIA_EXTENSIONS))
            ->when(($filters['type'] ?? null) === 'document', fn ($query) => $query->whereNotIn('file_extension', self::MEDIA_EXTENSIONS))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = Archive::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN file_extension IN ('jpg', 'jpeg', 'png') THEN 1 ELSE 0 END) as media")
            ->selectRaw('SUM(CASE WHEN confidentiality = ? THEN 1 ELSE 0 END) as confidential', [ArchiveConfidentiality::Confidential->value])
            ->first();

        $editingArchiveId = (int) $request->old('archive_context_id', 0);
        $editingArchive = $editingArchiveId > 0
            ? Archive::query()->find($editingArchiveId)
            : null;

        return view('admin.arsip', [
            'archives' => $archives,
            'filters' => $filters,
            'operationalCategories' => OperationalCategory::cases(),
            'confidentialities' => ArchiveConfidentiality::cases(),
            'archiveSummary' => [
                'total' => (int) $summary->total,
                'documents' => (int) $summary->total - (int) $summary->media,
                'media' => (int) $summary->media,
                'confidential' => (int) $summary->confidential,
            ],
            'editingArchive' => $editingArchive,
        ]);
    }

    public function store(UpsertArchiveRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('archives/files', 'local');

        try {
            DB::transaction(function () use ($request, $file, $path): void {
                $archive = Archive::create([
                    ...$request->safe()->only(['title', 'operational_category', 'description', 'confidentiality']),
                    'created_by_user_id' => $request->user()->id,
                    ...$this->fileAttributes($file, $path),
                ]);

                $this->writeAuditLog($request, $archive, 'created', "Arsip {$archive->title} diunggah.");
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()->route('admin.arsip')->with('status', 'Arsip berhasil diunggah.');
    }

    public function update(UpsertArchiveRequest $request, Archive $archive): RedirectResponse
    {
        $file = $request->file('file');
        $newPath = $file?->store('archives/files', 'local');
        $oldPath = $archive->file_path;

        try {
            DB::transaction(function () use ($request, $archive, $file, $newPath): void {
                $attributes = $request->safe()->only(['title', 'operational_category', 'description', 'confidentiality']);

                if ($file !== null && $newPath !== null) {
                    $attributes = [...$attributes, ...$this->fileAttributes($file, $newPath)];
                }

                $archive->update($attributes);
                $this->writeAuditLog($request, $archive, 'updated', "Arsip {$archive->title} diperbarui.");
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('local')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath !== null) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('admin.arsip')->with('status', 'Arsip berhasil diperbarui.');
    }

    public function show(Archive $archive): StreamedResponse
    {
        Gate::authorize('access-admin');
        $this->ensureFileExists($archive);

        $disposition = in_array($archive->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true)
            ? 'inline'
            : 'attachment';

        return Storage::disk('local')->response(
            $archive->file_path,
            $archive->original_name,
            $this->secureHeaders($archive),
            $disposition,
        );
    }

    public function download(Archive $archive): StreamedResponse
    {
        Gate::authorize('access-admin');
        $this->ensureFileExists($archive);

        return Storage::disk('local')->download(
            $archive->file_path,
            $archive->original_name,
            $this->secureHeaders($archive),
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
    private function secureHeaders(Archive $archive): array
    {
        return [
            'Content-Type' => $archive->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'private, no-store',
        ];
    }

    private function ensureFileExists(Archive $archive): void
    {
        if (! Storage::disk('local')->exists($archive->file_path)) {
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
            'new_values' => ['file_path' => $archive->file_path],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
