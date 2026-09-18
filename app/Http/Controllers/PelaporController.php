<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestReportRequest;
use App\Http\Requests\StoreReportFeedbackRequest;
use App\Models\GuestAccess;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportStatus;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class PelaporController extends Controller
{
    public function index(): View
    {
        return view('pelapor.index');
    }

    public function lapor(): View
    {
        return view('pelapor.lapor');
    }

    public function store(StoreGuestReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedEvidence = [];

        try {
            foreach ($request->file('bukti', []) as $file) {
                $path = $file->store('reports/evidence', 'local');

                if ($path === false) {
                    throw new RuntimeException('Bukti laporan gagal disimpan.');
                }

                $storedEvidence[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                ];
            }

            [$guestAccess, $rawToken] = $this->resolveGuestAccess($request);
            $report = DB::transaction(function () use ($validated, $guestAccess, $storedEvidence): Report {
                $report = Report::create([
                    'code' => $this->generateReportCode(),
                    'guest_access_id' => $guestAccess->id,
                    'created_by_user_id' => null,
                    'type' => $validated['jenis'],
                    'category' => null,
                    'source' => 'online',
                    'source_platform' => 'website',
                    'reporter_name' => null,
                    'reporter_phone' => $validated['telepon'],
                    'reporter_nik' => $validated['nik'] ?? null,
                    'description' => $validated['deskripsi'],
                    'location_address' => $validated['lokasi'],
                    'latitude' => null,
                    'longitude' => null,
                    'status' => ReportStatus::Submitted,
                ]);

                $report->statusHistories()->create([
                    'changed_by_user_id' => null,
                    'status' => ReportStatus::Submitted,
                    'note' => 'Laporan dibuat oleh masyarakat melalui website.',
                ]);

                foreach ($storedEvidence as $evidence) {
                    $report->attachments()->create([
                        'uploaded_by_user_id' => null,
                        'file_path' => $evidence['path'],
                        'original_name' => $evidence['original_name'],
                        'file_type' => 'image',
                        'mime_type' => $evidence['mime_type'],
                        'file_size' => $evidence['file_size'],
                        'audience' => 'public',
                    ]);
                }

                return $report;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_column($storedEvidence, 'path'));

            throw $exception;
        }

        $administrators = User::query()
            ->where('role', UserRole::Admin)
            ->where('status', UserStatus::Active)
            ->get();
        Notification::send($administrators, new NewReportNotification($report));

        return redirect()
            ->route('pelapor.lapor')
            ->withCookie(cookie('sipraja_guest_token', $rawToken, 525600, null, null, $request->isSecure(), true, false, 'lax'))
            ->with('report_code', $report->code);
    }

    public function status(Request $request): View
    {
        $reports = collect();
        $rawToken = $request->cookie('sipraja_guest_token');

        if (is_string($rawToken) && $rawToken !== '') {
            $guestAccess = $this->guestAccessFromRequest($request);

            if ($guestAccess !== null) {
                $guestAccess->update(['last_used_at' => now()]);
                $reports = Report::query()
                    ->whereBelongsTo($guestAccess)
                    ->with('statusHistories')
                    ->latest()
                    ->get()
                    ->map(fn (Report $report): array => $this->serializeReport($report));
            }
        }

        return view('pelapor.status', ['reports' => $reports->values()]);
    }

    public function feedback(): View
    {
        return view('pelapor.feedback');
    }

    public function storeFeedback(StoreReportFeedbackRequest $request, Report $report): JsonResponse
    {
        $guestAccess = $this->guestAccessFromRequest($request);

        if ($guestAccess === null || (int) $report->guest_access_id !== (int) $guestAccess->id) {
            throw new NotFoundHttpException;
        }

        DB::transaction(function () use ($request, $report): void {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReport->status !== ReportStatus::Completed) {
                throw new NotFoundHttpException;
            }

            if ($lockedReport->feedback_submitted_at !== null) {
                throw new HttpResponseException(
                    response()->json(['message' => 'Feedback untuk laporan ini sudah pernah dikirim.'], 409),
                );
            }

            $validated = $request->validated();
            $lockedReport->update([
                'feedback_rating' => $validated['rating'],
                'feedback_comment' => $validated['comment'] ?? null,
                'feedback_submitted_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Feedback berhasil disimpan.']);
    }

    /** @return array{0: GuestAccess, 1: string} */
    private function resolveGuestAccess(Request $request): array
    {
        $rawToken = $request->cookie('sipraja_guest_token');

        if (is_string($rawToken) && $rawToken !== '') {
            $guestAccess = $this->guestAccessFromRequest($request);

            if ($guestAccess !== null) {
                return [$guestAccess, $rawToken];
            }
        }

        $rawToken = Str::random(64);

        return [GuestAccess::create([
            'token_hash' => hash('sha256', $rawToken),
            'last_used_at' => now(),
            'expires_at' => now()->addYear(),
        ]), $rawToken];
    }

    private function guestAccessFromRequest(Request $request): ?GuestAccess
    {
        $rawToken = $request->cookie('sipraja_guest_token');

        if (! is_string($rawToken) || $rawToken === '') {
            return null;
        }

        return GuestAccess::query()
            ->where('token_hash', hash('sha256', $rawToken))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    private function generateReportCode(): string
    {
        do {
            $code = 'SPJ-'.strtoupper(Str::random(6));
        } while (Report::query()->where('code', $code)->exists());

        return $code;
    }

    /** @return array<string, mixed> */
    private function serializeReport(Report $report): array
    {
        $statusIndex = match ($report->status) {
            ReportStatus::Completed => 5,
            ReportStatus::Rejected => 6,
            default => array_search($report->status, ReportStatus::cases(), true),
        };

        $rejectionReason = $report->statusHistories
            ->firstWhere('status', ReportStatus::Rejected)?->note;

        return [
            'kode' => $report->code,
            'jenis' => $report->type->value,
            'kategori' => $report->category?->label(),
            'deskripsi' => $report->description,
            'lokasi' => $report->location_address,
            'telepon' => $report->reporter_phone,
            'dibuat' => $report->created_at?->format('d/m/Y H.i'),
            'statusIdx' => $statusIndex === false ? 0 : $statusIndex,
            'alasanTolak' => $rejectionReason,
            'feedbackSubmitted' => $report->feedback_submitted_at !== null,
            'feedbackRating' => $report->feedback_rating,
            'feedbackComment' => $report->feedback_comment,
            'feedbackUrl' => route('pelapor.feedback.store', $report),
        ];
    }
}
