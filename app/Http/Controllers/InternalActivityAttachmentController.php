<?php

namespace App\Http\Controllers;

use App\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class InternalActivityAttachmentController extends Controller
{
    /** @var list<string> */
    private const PREVIEWABLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function show(Activity $activity, Attachment $attachment): StreamedResponse
    {
        $this->ensureAccessible($activity, $attachment);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            throw new NotFoundHttpException;
        }

        return Storage::disk('local')->response(
            $attachment->file_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
                'Cache-Control' => 'private, no-store',
            ],
            'inline',
        );
    }

    public function destroy(Request $request, Activity $activity, Attachment $attachment): RedirectResponse
    {
        $this->ensureAccessible($activity, $attachment);

        /** @var User $user */
        $user = $request->user();
        $originalPath = $attachment->file_path;
        $quarantinePath = 'activities/quarantine/'.Str::uuid();
        $movedToQuarantine = false;

        try {
            DB::transaction(function () use (
                $activity,
                $attachment,
                $originalPath,
                $quarantinePath,
                &$movedToQuarantine,
                $request,
                $user,
            ): void {
                $lockedActivity = Activity::query()
                    ->whereKey($activity->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedAttachment = Attachment::query()
                    ->whereKey($attachment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureAccessible($lockedActivity, $lockedAttachment);

                if (Storage::disk('local')->exists($originalPath)) {
                    if (! Storage::disk('local')->move($originalPath, $quarantinePath)) {
                        throw new RuntimeException('Dokumentasi gagal dipindahkan sebelum penghapusan.');
                    }

                    $movedToQuarantine = true;
                }

                $lockedAttachment->delete();

                AuditLog::create([
                    'actor_user_id' => $user->id,
                    'subject_type' => 'activity',
                    'subject_id' => $lockedActivity->id,
                    'module' => 'activities',
                    'action' => 'attachment_deleted',
                    'description' => "Dokumentasi {$lockedAttachment->original_name} dihapus dari kegiatan internal {$lockedActivity->name}.",
                    'old_values' => [
                        'attachment_id' => $lockedAttachment->id,
                        'original_name' => $lockedAttachment->original_name,
                        'file_path' => $lockedAttachment->file_path,
                    ],
                    'new_values' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($movedToQuarantine && Storage::disk('local')->exists($quarantinePath)) {
                Storage::disk('local')->move($quarantinePath, $originalPath);
            }

            throw $exception;
        }

        if ($movedToQuarantine) {
            Storage::disk('local')->delete($quarantinePath);
        }

        $redirectRoute = $request->string('return_to')->toString() === 'edit'
            ? 'admin.kegiatan.edit'
            : 'admin.kegiatan.show';

        return redirect()
            ->route($redirectRoute, $activity)
            ->with('status', 'Dokumentasi kegiatan berhasil dihapus permanen.');
    }

    private function ensureAccessible(Activity $activity, Attachment $attachment): void
    {
        if (
            $activity->type !== ActivityType::Internal
            || $attachment->attachable_type !== 'activity'
            || $attachment->attachable_id !== $activity->id
            || $attachment->audience !== 'internal'
            || $attachment->file_type !== 'image'
            || ! in_array($attachment->mime_type, self::PREVIEWABLE_MIME_TYPES, true)
        ) {
            throw new NotFoundHttpException;
        }
    }
}
