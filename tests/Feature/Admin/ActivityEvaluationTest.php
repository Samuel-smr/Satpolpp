<?php

namespace Tests\Feature\Admin;

use App\ActivityType;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_lists_latest_unevaluated_external_activities_from_reports(): void
    {
        $admin = User::factory()->create();
        $olderReport = Report::factory()->create(['code' => 'LAP-OLDER']);
        $latestReport = Report::factory()->create(['code' => 'LAP-LATEST']);
        $olderActivity = Activity::factory()->create([
            'report_id' => $olderReport->id,
            'type' => ActivityType::External,
            'name' => 'Penanganan Lama',
            'activity_date' => '2026-09-01',
        ]);
        $latestActivity = Activity::factory()->create([
            'report_id' => $latestReport->id,
            'type' => ActivityType::External,
            'name' => 'Penanganan Terbaru',
            'activity_date' => '2026-09-09',
        ]);
        $internalActivity = Activity::factory()->create([
            'type' => ActivityType::Internal,
            'name' => 'Patroli Internal',
            'category' => 'Patroli Ketertiban Umum',
        ]);
        ActivityTemplate::factory()->create([
            'name' => 'Patroli Ketertiban Umum',
            'sort_order' => 1,
        ]);
        ActivityTemplate::factory()->create([
            'name' => 'Kategori Tanpa Kegiatan',
            'sort_order' => 2,
        ]);
        Activity::factory()->create([
            'type' => ActivityType::External,
            'report_id' => null,
            'name' => 'Eksternal Tanpa Laporan',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.evaluasi'));

        $response->assertOk();
        $response->assertSeeInOrder([$latestActivity->name, $olderActivity->name]);
        $response->assertSeeText('LAP-LATEST');
        $response->assertSee('id="evaluation-category"', false);
        $response->assertSeeText('Patroli Ketertiban Umum');
        $response->assertSeeText('Kategori Tanpa Kegiatan');
        $response->assertSee('data-category="Patroli Ketertiban Umum"', false);
        $response->assertSee('const kategori = kategoriEvaluasi.value;', false);
        $response->assertSee('sumberEksternal', false);
        $response->assertSee('kategoriEvaluasi.disabled = sumberEksternal;', false);
        $response->assertSee('Belum ada kegiatan pada kategori ini.', false);
        $response->assertDontSeeText('Eksternal Tanpa Laporan');
        $response->assertDontSeeText('Tindak lanjut laporan PKL liar');
    }

    public function test_admin_can_evaluate_an_external_activity_from_a_report(): void
    {
        $this->travelTo('2026-09-09 14:30:00');
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.evaluasi.store'), [
            'activity_id' => $activity->id,
            'evaluation_notes' => 'Penanganan selesai dan kondisi kembali tertib.',
            'evaluation_obstacles' => 'Koordinasi lintas instansi membutuhkan waktu.',
        ]);

        $response->assertRedirectToRoute('admin.evaluasi');
        $response->assertSessionHas('status', 'Evaluasi kegiatan berhasil disimpan.');
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'evaluated_by_user_id' => $admin->id,
            'evaluation_notes' => 'Penanganan selesai dan kondisi kembali tertib.',
            'evaluation_obstacles' => 'Koordinasi lintas instansi membutuhkan waktu.',
            'evaluated_at' => '2026-09-09 14:30:00',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $activity->id,
            'module' => 'activity_evaluations',
            'action' => 'evaluated',
        ]);
    }

    public function test_an_activity_cannot_be_evaluated_twice(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create([
            'evaluated_at' => now(),
            'evaluation_notes' => 'Evaluasi pertama',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.evaluasi'))->post(route('admin.evaluasi.store'), [
            'activity_id' => $activity->id,
            'evaluation_notes' => 'Evaluasi kedua',
        ]);

        $response->assertRedirect(route('admin.evaluasi'));
        $response->assertInvalid(['activity_id']);
        $this->assertSame('Evaluasi pertama', $activity->fresh()->evaluation_notes);
    }

    public function test_external_activity_without_a_report_cannot_be_evaluated(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create([
            'type' => ActivityType::External,
            'report_id' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.evaluasi.store'), [
            'activity_id' => $activity->id,
            'evaluation_notes' => 'Tidak boleh tersimpan.',
        ]);

        $response->assertInvalid(['activity_id']);
        $this->assertNull($activity->fresh()->evaluated_at);
    }

    public function test_admin_can_update_an_existing_evaluation(): void
    {
        $this->travelTo('2026-09-09 15:00:00');
        $admin = User::factory()->create();
        $activity = Activity::factory()->create([
            'evaluation_notes' => 'Hasil lama',
            'evaluation_obstacles' => 'Kendala lama',
            'evaluated_at' => '2026-09-08 10:00:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.evaluasi.update', $activity), [
            'evaluation_context_id' => $activity->id,
            'evaluation_notes' => 'Hasil evaluasi diperbarui',
            'evaluation_obstacles' => null,
        ]);

        $response->assertRedirectToRoute('admin.evaluasi');
        $response->assertSessionHas('status', 'Evaluasi kegiatan berhasil diperbarui.');
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'evaluation_notes' => 'Hasil evaluasi diperbarui',
            'evaluation_obstacles' => null,
            'evaluated_by_user_id' => $admin->id,
            'evaluated_at' => '2026-09-09 15:00:00',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $activity->id,
            'module' => 'activity_evaluations',
            'action' => 'updated',
        ]);
    }

    public function test_admin_can_remove_an_evaluation_without_deleting_its_activity_or_report(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->create();
        $activity = Activity::factory()->create([
            'report_id' => $report->id,
            'type' => ActivityType::External,
            'evaluated_by_user_id' => $admin->id,
            'evaluation_notes' => 'Evaluasi yang akan dihapus',
            'evaluation_obstacles' => 'Kendala',
            'evaluated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.evaluasi.destroy', $activity));

        $response->assertRedirectToRoute('admin.evaluasi');
        $response->assertSessionHas('status', 'Evaluasi kegiatan berhasil dihapus.');
        $activity->refresh();
        $this->assertNull($activity->evaluation_notes);
        $this->assertNull($activity->evaluation_obstacles);
        $this->assertNull($activity->evaluated_by_user_id);
        $this->assertNull($activity->evaluated_at);
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'report_id' => $report->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $activity->id,
            'module' => 'activity_evaluations',
            'action' => 'deleted',
        ]);
    }

    public function test_unevaluated_activity_cannot_use_update_or_delete_evaluation_endpoints(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->put(route('admin.evaluasi.update', $activity), [
            'evaluation_notes' => 'Tidak boleh diperbarui',
        ])->assertForbidden();

        $this->actingAs($admin)->delete(route('admin.evaluasi.destroy', $activity))->assertNotFound();
        $this->assertNull($activity->fresh()->evaluated_at);
    }
}
