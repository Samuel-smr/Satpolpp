<?php

namespace App\Http\Controllers;

use App\Support\DataYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->where('created_at', '>=', app(DataYear::class)->start())->where('created_at', '<', app(DataYear::class)->end())->latest()->limit(10)->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notifikasi'),
                'message' => data_get($notification->data, 'message', '-'),
                'time' => $notification->created_at->diffForHumans(),
                'unread' => $notification->read_at === null,
            ]);

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->where('created_at', '>=', app(DataYear::class)->start())->where('created_at', '<', app(DataYear::class)->end())->count(),
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $databaseNotification */
        $databaseNotification = $request->user()
            ->notifications()->where('created_at', '>=', app(DataYear::class)->start())->where('created_at', '<', app(DataYear::class)->end())
            ->whereKey($notification)
            ->firstOrFail();

        $databaseNotification->markAsRead();

        $destination = data_get($databaseNotification->data, 'url');

        return is_string($destination)
            && str_starts_with($destination, '/')
            && ! str_starts_with($destination, '//')
            && ! str_contains($destination, '\\')
            && ! preg_match('/[\x00-\x20]/', $destination)
            ? redirect()->to($destination)
            : redirect()->to('/');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->where('created_at', '>=', app(DataYear::class)->start())->where('created_at', '<', app(DataYear::class)->end())->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }
}
