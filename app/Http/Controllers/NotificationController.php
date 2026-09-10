<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->limit(10)->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notifikasi'),
                'message' => data_get($notification->data, 'message', '-'),
                'time' => $notification->created_at->diffForHumans(),
                'unread' => $notification->read_at === null,
            ]);

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $databaseNotification */
        $databaseNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $databaseNotification->markAsRead();

        $destination = data_get($databaseNotification->data, 'url');

        return is_string($destination) && str_starts_with($destination, '/')
            ? redirect()->to($destination)
            : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }
}
