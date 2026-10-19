<?php

namespace App\Notifications;

use App\Models\Report;
use App\UserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Laporan baru',
            'message' => "Laporan {$this->report->code} baru diterima dari masyarakat.",
            'report_id' => $this->report->id,
            'url' => route($notifiable->role === UserRole::SuperAdmin ? 'super_admin.pengaduan.detail' : 'admin.pengaduan.detail', $this->report, absolute: false),
        ];
    }
}
