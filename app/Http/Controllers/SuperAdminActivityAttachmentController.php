<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SuperAdminActivityAttachmentController extends Controller
{
    /** @var list<string> */
    private const PREVIEWABLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function show(Activity $activity, Attachment $attachment): StreamedResponse
    {
        if (
            $attachment->attachable_type !== 'activity'
            || $attachment->attachable_id !== $activity->id
            || $attachment->audience !== 'internal'
            || $attachment->file_type !== 'image'
            || ! in_array($attachment->mime_type, self::PREVIEWABLE_MIME_TYPES, true)
            || ! Storage::disk('local')->exists($attachment->file_path)
        ) {
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
}
