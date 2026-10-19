<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportNotification;
use App\ReportSource;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DataYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2027-07-15 10:00:00');
    }

    public function test_each_login_clears_the_previous_selection_and_displays_the_year_picker(): void
    {
        $admin = User::factory()->create();

        $this->withSession(['data_year' => 2026])->post(route('login.store'), [
            'identifier' => $admin->username,
            'password' => 'password',
        ])->assertRedirectToRoute('admin.dashboard')->assertSessionMissing('data_year');
        $this->get(route('admin.dashboard'))->assertOk()->assertSeeText('Pilih Tahun Data')->assertSee('value="2027"', false)->assertSee('value="2026"', false);

        $this->put(route('data-year.update'), ['data_year' => 2026])->assertRedirectToRoute('admin.dashboard')->assertSessionHas('data_year', 2026);
        $this->get(route('admin.dashboard'))->assertOk()->assertSeeText('Data 2026')->assertSeeText('Pengaduan Tahun 2026');
        $this->post(route('logout'));
        $this->post(route('login.store'), ['identifier' => $admin->username, 'password' => 'password'])->assertSessionMissing('data_year');
    }

    public function test_the_new_year_appears_automatically_and_invalid_years_do_not_change_the_selection(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin)->withSession(['data_year' => 2026]);

        foreach ([2028, 2000, 'invalid', null] as $invalidYear) {
            $this->put(route('data-year.update'), ['data_year' => $invalidYear])->assertInvalid('data_year')->assertSessionHas('data_year', 2026);
        }

        $this->travelTo('2028-01-01 00:00:01');
        $this->put(route('data-year.update'), ['data_year' => 2028])->assertRedirectToRoute('super_admin.dashboard')->assertSessionHas('data_year', 2028);
        $this->get(route('super_admin.dashboard'))->assertOk()->assertSee('value="2028"', false);
    }

    public function test_year_selection_requires_an_active_internal_account(): void
    {
        $this->put(route('data-year.update'), ['data_year' => 2026])->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->blocked()->create())->put(route('data-year.update'), ['data_year' => 2026])->assertForbidden();
    }

    /** @return array<string, array{bool}> */
    public static function internalRoles(): array
    {
        return ['admin' => [false], 'super admin' => [true]];
    }

    #[DataProvider('internalRoles')]
    public function test_report_lists_statistics_and_detail_urls_follow_the_selected_year(bool $isSuperAdmin): void
    {
        $user = $isSuperAdmin ? User::factory()->superAdmin()->create() : User::factory()->create();
        $prefix = $isSuperAdmin ? 'super_admin' : 'admin';
        $oldReport = Report::factory()->create(['created_at' => '2026-01-01 00:00:00', 'status' => ReportStatus::Completed]);
        $endOfYearReport = Report::factory()->create(['created_at' => '2026-12-31 23:59:59']);
        $currentReport = Report::factory()->create(['created_at' => '2027-01-01 00:00:00']);

        $this->actingAs($user)->withSession(['data_year' => 2026])->get(route($prefix.'.pengaduan'))
            ->assertOk()->assertSeeText($oldReport->code)->assertSeeText($endOfYearReport->code)->assertDontSeeText($currentReport->code)
            ->assertViewHas('statistics', ['total' => 2, 'completed' => 1]);
        $this->get(route($prefix.'.pengaduan.detail', $oldReport))->assertOk();
        $this->get(route($prefix.'.pengaduan.detail', $currentReport))->assertNotFound();
    }

    #[DataProvider('internalRoles')]
    public function test_activity_lists_and_export_data_use_the_activity_year(bool $isSuperAdmin): void
    {
        $user = $isSuperAdmin ? User::factory()->superAdmin()->create() : User::factory()->create();
        $prefix = $isSuperAdmin ? 'super_admin' : 'admin';
        $oldActivity = Activity::factory()->create(['activity_date' => '2026-05-20', 'created_at' => now()]);
        $currentActivity = Activity::factory()->create(['activity_date' => '2027-01-01', 'created_at' => '2026-12-31']);

        $this->actingAs($user)->withSession(['data_year' => 2026])->get(route($prefix.'.kegiatan'))
            ->assertOk()->assertSeeText($oldActivity->name)->assertDontSeeText($currentActivity->name)
            ->assertViewHas('exportActivities', fn ($activities): bool => $activities->pluck('id')->all() === [$oldActivity->id]);
        $this->get(route($prefix.'.kegiatan.show', $currentActivity))->assertNotFound();
        $this->put(route($prefix.'.kegiatan.update', $currentActivity), [])->assertNotFound();
        $this->delete(route($prefix.'.kegiatan.destroy', $currentActivity))->assertNotFound();
        $this->assertNotSoftDeleted($currentActivity);
    }

    public function test_dashboard_maps_feedback_evaluations_and_search_are_scoped_to_the_selected_year(): void
    {
        $admin = User::factory()->create();
        $oldReport = Report::factory()->assignedTo($admin)->create(['code' => 'SPJ-OLD', 'created_at' => '2026-12-31', 'feedback_rating' => 5, 'feedback_comment' => 'Feedback tahun lama', 'feedback_submitted_at' => now()]);
        $newReport = Report::factory()->create(['code' => 'SPJ-NEW', 'feedback_rating' => 1, 'feedback_comment' => 'Feedback tahun baru', 'feedback_submitted_at' => now()]);
        $oldActivity = Activity::factory()->create(['name' => 'Evaluasi tahun lama', 'activity_date' => '2026-12-31', 'evaluated_at' => now()]);
        Activity::factory()->create(['name' => 'Evaluasi tahun baru', 'evaluated_at' => now()]);

        $this->actingAs($admin)->withSession(['data_year' => 2026]);
        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('latestReports', fn ($reports): bool => $reports->modelKeys() === [$oldReport->id]);
        $this->get(route('admin.feedback'))->assertOk()->assertSeeText('Feedback tahun lama')->assertDontSeeText('Feedback tahun baru')->assertViewHas('feedbackSummary', fn ($summary): bool => $summary['total'] === 1);
        $this->get(route('admin.evaluasi'))->assertOk()->assertSeeText($oldActivity->name)->assertDontSeeText('Evaluasi tahun baru');
        $this->getJson(route('global-search', ['q' => 'SPJ-']))->assertOk()->assertJsonFragment(['title' => $oldReport->code])->assertJsonMissing(['title' => $newReport->code]);

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.dashboard'))
            ->assertOk()->assertViewHas('systemStats', fn ($stats): bool => $stats['yearly_reports'] === 1 && $stats['yearly_activities'] === 1)
            ->assertViewHas('mapPoints', fn ($points): bool => $points->pluck('id')->all() === ['L-'.$oldReport->id, 'K-'.$oldActivity->id]);
    }

    public function test_report_mutations_and_files_cannot_cross_the_selected_year(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->assignedTo($admin)->create();
        $attachment = $report->attachments()->create(['file_path' => 'test.jpg', 'original_name' => 'test.jpg', 'file_type' => 'image', 'mime_type' => 'image/jpeg', 'file_size' => 1, 'audience' => 'public']);

        $this->actingAs($admin)->withSession(['data_year' => 2026]);
        $this->patch(route('admin.pengaduan.status.update', $report), ['current_status' => 'submitted'])->assertNotFound();
        $this->patch(route('admin.pengaduan.reject', $report), [])->assertNotFound();
        $this->post(route('admin.pengaduan.complete.store', $report), [])->assertNotFound();
        $this->delete(route('admin.pengaduan.destroy', $report))->assertNotFound();
        $this->get(route('admin.pengaduan.attachments.show', [$report, $attachment]))->assertNotFound();
        $this->assertSame(ReportStatus::Submitted, $report->fresh()->status);
        $this->assertNotSoftDeleted($report);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_archives_and_downloads_follow_the_archive_creation_year(): void
    {
        $admin = User::factory()->create();
        $oldArchive = Archive::factory()->create(['title' => 'Arsip Tahun Lama', 'created_at' => '2026-08-01']);
        $newArchive = Archive::factory()->create(['title' => 'Arsip Tahun Baru']);
        $file = ArchiveFile::factory()->for($newArchive)->create();

        $this->actingAs($admin)->withSession(['data_year' => 2026])->get(route('admin.arsip'))
            ->assertOk()->assertSeeText($oldArchive->title)->assertDontSeeText($newArchive->title)
            ->assertViewHas('archiveSummary', fn ($summary): bool => $summary['total'] === 1);
        $this->get(route('admin.arsip.download', [$newArchive, $file]))->assertNotFound();
        $this->delete(route('admin.arsip.destroy', $newArchive))->assertNotFound();
        $this->assertNotSoftDeleted($newArchive);
    }

    public function test_users_and_officers_remain_available_across_years(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->create(['name' => 'Admin Lintas Tahun']);
        $this->employee(['name' => 'Petugas Lintas Tahun']);

        $this->actingAs($superAdmin)->withSession(['data_year' => 2026]);
        $this->get(route('super_admin.user'))->assertOk()->assertSeeText($admin->name);
        $this->get(route('super_admin.petugas'))->assertOk()->assertSeeText('Petugas Lintas Tahun');
    }

    public function test_notifications_and_marking_as_read_follow_the_selected_year(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $admin->notify(new NewReportNotification($report));
        $currentNotification = $admin->notifications()->sole();
        $oldNotification = $currentNotification->replicate();
        $oldNotification->id = (string) Str::uuid();
        $oldNotification->created_at = '2026-12-31 23:59:59';
        $oldNotification->save();

        $this->actingAs($admin)->withSession(['data_year' => 2026])->getJson(route('notifications.index'))
            ->assertOk()->assertJsonPath('unread_count', 1)->assertJsonPath('notifications.0.id', $oldNotification->id);
        $this->post(route('notifications.read', $currentNotification->id))->assertNotFound();
        $this->post(route('notifications.read-all'))->assertRedirect();
        $this->assertNotNull($oldNotification->fresh()->read_at);
        $this->assertNull($currentNotification->fresh()->read_at);
    }

    public function test_new_records_cannot_be_silently_created_outside_the_selected_year(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $template = ActivityTemplate::factory()->create();
        $employee = $this->employee();
        $this->actingAs($admin)->withSession(['data_year' => 2026]);

        $this->post(route('admin.pengaduan.store'), [
            'source' => ReportSource::Offline->value, 'reporter_phone' => '081234567890',
            'description' => 'Laporan baru.', 'location_address' => 'Wates', 'latitude' => -7.85, 'longitude' => 110.16,
        ])->assertInvalid('data_year');
        $this->post(route('admin.arsip.store'), [
            'title' => 'Arsip baru', 'operational_category' => $template->name, 'confidentiality' => 'public',
            'files' => [UploadedFile::fake()->create('arsip.pdf', 10, 'application/pdf')],
        ])->assertInvalid('data_year');
        $this->post(route('admin.kegiatan.store'), [
            'name' => 'Kegiatan baru', 'category' => $template->name, 'activity_date' => '2027-01-01',
            'location_address' => 'Wates', 'latitude' => -7.85, 'longitude' => 110.16, 'employee_nips' => [$employee->employee_number],
        ])->assertInvalid('activity_date');
        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('archives', 0);
        $this->assertDatabaseCount('activities', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_updating_an_old_report_preserves_its_original_year_and_public_tracking_is_unfiltered(): void
    {
        $admin = User::factory()->create();
        $oldReport = Report::factory()->assignedTo($admin)->create(['created_at' => '2026-12-31 23:59:59']);
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
        Http::preventStrayRequests();
        Http::fake(['http://localhost:3456/send' => Http::response(['status' => true])]);

        $this->actingAs($admin)->withSession(['data_year' => 2026])->patch(route('admin.pengaduan.status.update', $oldReport), ['current_status' => 'submitted'])->assertRedirect();
        $this->assertSame(2026, $oldReport->fresh()->created_at->year);
        $this->withSession(['data_year' => 2027])->get(route('admin.pengaduan.detail', $oldReport))->assertNotFound();

        $token = 'browser-tracking-token';
        $oldReport->guestAccess->update(['token_hash' => hash('sha256', $token)]);
        $this->withCookie('sipraja_guest_token', $token)->get(route('pelapor.status'))->assertOk()->assertSee($oldReport->code, false);
    }
}
