<?php

namespace Tests\Feature\Admin;

use App\ActivityType;
use App\FindingType;
use App\FindingUnit;
use App\Models\Activity;
use App\Models\ActivityTemplate;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Officer;
use App\Models\Report;
use App\Models\User;
use App\OperationalCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternalActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_lists_only_active_templates_and_officers(): void
    {
        $admin = User::factory()->create();
        $activeTemplate = ActivityTemplate::factory()->create([
            'name' => 'Template Aktif',
            'operational_category' => OperationalCategory::EventSecurity,
        ]);
        ActivityTemplate::factory()->inactive()->create(['name' => 'Template Nonaktif']);
        $activeOfficer = Officer::factory()->create(['name' => 'Petugas Aktif']);
        Officer::factory()->inactive()->create(['name' => 'Petugas Nonaktif']);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan.create'));

        $response->assertOk();
        $response->assertSeeText($activeTemplate->name);
        $response->assertDontSeeText('Template Nonaktif');
        $response->assertSeeText($activeOfficer->name);
        $response->assertDontSeeText('Petugas Nonaktif');
        $response->assertSee('name="category"', false);
        $response->assertSee('data-category="Template Aktif"', false);
        $response->assertSee('id="activity-location-search"', false);
        $response->assertSee('id="activity-current-location-button"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'name="documentation[]"'));
        $response->assertSee('multiple', false);
        $response->assertSee('id="documentation-preview"', false);
        $response->assertSee('Hapus foto terpilih', false);
    }

    public function test_admin_can_create_internal_activity_with_officers_and_internal_documentation(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-09-09 10:00:00');
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        $report = Report::factory()->create();
        $template = ActivityTemplate::factory()->create([
            'operational_category' => OperationalCategory::EventSecurity,
        ]);
        $officers = Officer::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post(route('admin.kegiatan.store'), [
            ...$this->validPayload($officers->pluck('id')->all(), $template->id),
            'category' => $template->name,
            'activity_date' => '2026-09-15',
            'findings' => [
                ['type' => FindingType::Person->value, 'quantity' => 2, 'unit' => FindingUnit::Person->value],
                ['type' => FindingType::Item->value, 'quantity' => 12, 'unit' => FindingUnit::Bottle->value],
            ],
            'documentation' => [
                UploadedFile::fake()->image('dokumentasi-satu.jpg'),
                UploadedFile::fake()->image('dokumentasi-dua.png'),
            ],
            'type' => ActivityType::External->value,
            'report_id' => $report->id,
            'created_by_user_id' => $otherUser->id,
        ]);

        $activity = Activity::query()->sole();
        $response->assertRedirectToRoute('admin.kegiatan.show', $activity);
        $response->assertSessionHas('status', 'Kegiatan internal berhasil disimpan.');
        $this->assertNull($activity->report_id);
        $this->assertSame($admin->id, $activity->created_by_user_id);
        $this->assertSame(ActivityType::Internal, $activity->type);
        $this->assertSame($template->id, $activity->activity_template_id);
        $this->assertSame($template->name, $activity->category);
        $this->assertSame('2026-09-15', $activity->activity_date->toDateString());
        $this->assertCount(2, $activity->findings);
        $this->assertSame(FindingType::Person, $activity->findings[0]->type);
        $this->assertSame(2, $activity->findings[0]->quantity);
        $this->assertSame(FindingUnit::Person, $activity->findings[0]->unit);
        $this->assertSame(FindingType::Item, $activity->findings[1]->type);
        $this->assertSame(12, $activity->findings[1]->quantity);
        $this->assertSame(FindingUnit::Bottle, $activity->findings[1]->unit);
        $this->assertDatabaseCount('activity_officer', 2);
        $this->assertDatabaseCount('attachments', 2);

        foreach ($activity->attachments as $attachment) {
            $this->assertSame('activity', $attachment->attachable_type);
            $this->assertSame('internal', $attachment->audience);
            $this->assertSame($admin->id, $attachment->uploaded_by_user_id);
            Storage::disk('local')->assertExists($attachment->file_path);
        }

        $firstAttachment = $activity->attachments->first();

        $this->actingAs($admin)
            ->get(route('admin.kegiatan.show', $activity))
            ->assertOk()
            ->assertDontSee('Kategori operasional')
            ->assertSeeText('Barang')
            ->assertSeeText('12')
            ->assertSeeText('Botol')
            ->assertSee(route('admin.kegiatan.attachments.show', [$activity, $firstAttachment]), false)
            ->assertSee('alt="Dokumentasi dokumentasi-satu.jpg"', false);

        $auditLog = AuditLog::query()->sole();
        $this->assertSame('activity', $auditLog->subject_type);
        $this->assertSame($activity->id, $auditLog->subject_id);
        $this->assertSame('created', $auditLog->action);
        $this->assertSame(['type' => ActivityType::Internal->value], $auditLog->new_values);
    }

    public function test_internal_activity_can_be_created_without_template_result_or_documentation(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $officer = Officer::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.kegiatan.store'),
            $this->validPayload([$officer->id]),
        );

        $activity = Activity::query()->sole();
        $response->assertRedirectToRoute('admin.kegiatan.show', $activity);
        $this->assertNull($activity->activity_template_id);
        $this->assertNull($activity->handling_result);
        $this->assertCount(0, $activity->findings);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('activities/documentation'));
    }

    public function test_inactive_references_and_too_many_documentation_files_are_rejected_without_writes(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $inactiveOfficer = Officer::factory()->inactive()->create();
        $inactiveTemplate = ActivityTemplate::factory()->inactive()->create();

        $response = $this->from(route('admin.kegiatan.create'))
            ->actingAs($admin)
            ->post(route('admin.kegiatan.store'), [
                ...$this->validPayload([$inactiveOfficer->id], $inactiveTemplate->id),
                'documentation' => [
                    UploadedFile::fake()->image('satu.jpg'),
                    UploadedFile::fake()->image('dua.jpg'),
                    UploadedFile::fake()->image('tiga.jpg'),
                    UploadedFile::fake()->image('empat.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.kegiatan.create'));
        $response->assertSessionHasErrors([
            'activity_template_id' => 'Template kegiatan tidak aktif atau tidak tersedia.',
            'officer_ids.0' => 'Petugas yang dipilih tidak aktif atau tidak tersedia.',
            'documentation' => 'Foto dokumentasi maksimal tiga file.',
        ]);
        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('activity_officer', 0);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('audit_logs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('activities/documentation'));
    }

    public function test_internal_activity_detail_uses_database_data_and_escapes_user_content(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pembuat']);
        $officer = Officer::factory()->create(['name' => 'Petugas Lapangan']);
        $unsafeName = '<script>alert("detail")</script>';
        $activity = Activity::factory()->for($admin, 'createdByUser')->create([
            'name' => $unsafeName,
            'type' => ActivityType::Internal,
            'description' => '<img src=x onerror=alert(1)>',
            'handling_result' => 'Kegiatan berjalan lancar.',
        ]);
        $activity->officers()->attach($officer, ['assignment_role' => 'Petugas Kegiatan']);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan.show', $activity));

        $response->assertOk();
        $response->assertSee($unsafeName);
        $response->assertDontSee($unsafeName, false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertSeeText('Kegiatan berjalan lancar.');
        $response->assertSeeText('Petugas Lapangan');
        $response->assertSeeText('Admin Pembuat');
    }

    public function test_edit_form_is_prefilled_with_current_activity_and_officers(): void
    {
        $admin = User::factory()->create();
        $selectedOfficer = Officer::factory()->create(['name' => 'Petugas Terpilih']);
        $activity = Activity::factory()->for($admin, 'createdByUser')->create([
            'name' => 'Nama Kegiatan Lama',
            'type' => ActivityType::Internal,
            'handling_result' => 'Hasil sementara.',
        ]);
        $activity->officers()->attach($selectedOfficer, ['assignment_role' => 'Petugas Kegiatan']);
        $activity->attachments()->create([
            'uploaded_by_user_id' => $admin->id,
            'file_path' => 'activities/documentation/lama.jpg',
            'original_name' => 'lama.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'audience' => 'internal',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan.edit', $activity));

        $response->assertOk();
        $response->assertSee('Nama Kegiatan Lama');
        $response->assertSee('Hasil sementara.');
        $response->assertSee('Petugas Terpilih');
        $response->assertSee('lama.jpg');
        $response->assertSee('Tersimpan 1 dari 3 foto.');
        $response->assertSee('id="activity-location-search"', false);
        $response->assertSee('id="activity-current-location-button"', false);
    }

    public function test_admin_can_update_internal_activity_sync_officers_and_append_documentation(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-09-09 10:00:00');
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        $report = Report::factory()->create();
        $oldOfficer = Officer::factory()->create();
        $newOfficer = Officer::factory()->create();
        $activity = Activity::factory()->for($admin, 'createdByUser')->create([
            'name' => 'Nama Lama',
            'type' => ActivityType::Internal,
            'category' => 'Kategori Lama',
            'activity_date' => '2026-09-10',
        ]);
        $activity->officers()->attach($oldOfficer, ['assignment_role' => 'Petugas Kegiatan']);
        Storage::disk('local')->put('activities/documentation/lama.jpg', 'old-file');
        $activity->attachments()->create([
            'uploaded_by_user_id' => $admin->id,
            'file_path' => 'activities/documentation/lama.jpg',
            'original_name' => 'lama.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_size' => 8,
            'audience' => 'internal',
        ]);
        $currentUpdatedAt = $activity->updated_at->format('Y-m-d H:i:s');
        $this->travelTo('2026-09-09 10:01:00');

        $response = $this->actingAs($admin)->put(route('admin.kegiatan.update', $activity), [
            ...$this->validPayload([$newOfficer->id]),
            'current_updated_at' => $currentUpdatedAt,
            'name' => 'Nama Kegiatan Baru',
            'activity_date' => '2026-09-20',
            'handling_result' => 'Kegiatan selesai dengan tertib.',
            'documentation' => [UploadedFile::fake()->image('baru.png')],
            'type' => ActivityType::External->value,
            'report_id' => $report->id,
            'created_by_user_id' => $otherUser->id,
        ]);

        $response->assertRedirectToRoute('admin.kegiatan.show', $activity);
        $response->assertSessionHas('status', 'Kegiatan internal berhasil diperbarui.');

        $freshActivity = $activity->fresh();
        $this->assertSame('Nama Kegiatan Baru', $freshActivity->name);
        $this->assertSame(ActivityType::Internal, $freshActivity->type);
        $this->assertNull($freshActivity->report_id);
        $this->assertSame($admin->id, $freshActivity->created_by_user_id);
        $this->assertSame('Kategori Manual', $freshActivity->category);
        $this->assertSame('2026-09-20', $freshActivity->activity_date->toDateString());
        $this->assertSame('Kegiatan selesai dengan tertib.', $freshActivity->handling_result);
        $this->assertDatabaseMissing('activity_officer', [
            'activity_id' => $activity->id,
            'officer_id' => $oldOfficer->id,
        ]);
        $this->assertDatabaseHas('activity_officer', [
            'activity_id' => $activity->id,
            'officer_id' => $newOfficer->id,
            'assignment_role' => 'Petugas Kegiatan',
        ]);
        $this->assertDatabaseCount('attachments', 2);
        Storage::disk('local')->assertExists('activities/documentation/lama.jpg');
        Storage::disk('local')->assertExists($freshActivity->attachments()->where('original_name', 'baru.png')->sole()->file_path);

        $auditLog = AuditLog::query()->sole();
        $this->assertSame('updated', $auditLog->action);
        $this->assertSame('Nama Lama', $auditLog->old_values['name']);
        $this->assertSame('Nama Kegiatan Baru', $auditLog->new_values['name']);
    }

    public function test_stale_update_is_rejected_and_new_documentation_is_cleaned(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-09-09 10:00:00');
        $admin = User::factory()->create();
        $officer = Officer::factory()->create();
        $activity = Activity::factory()->create([
            'name' => 'Nama Terkini',
            'type' => ActivityType::Internal,
        ]);
        $currentUpdatedAt = $activity->updated_at->format('Y-m-d H:i:s');
        $this->travelTo('2026-09-09 10:01:00');
        $activity->update(['description' => 'Perubahan dari admin lain.']);

        $response = $this->from(route('admin.kegiatan.edit', $activity))
            ->actingAs($admin)
            ->put(route('admin.kegiatan.update', $activity), [
                ...$this->validPayload([$officer->id]),
                'current_updated_at' => $currentUpdatedAt,
                'name' => 'Perubahan dari formulir lama',
                'documentation' => [UploadedFile::fake()->image('stale.jpg')],
            ]);

        $response->assertRedirect(route('admin.kegiatan.edit', $activity));
        $response->assertSessionHasErrors('activity');
        $this->assertSame('Nama Terkini', $activity->fresh()->name);
        $this->assertSame('Perubahan dari admin lain.', $activity->fresh()->description);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('audit_logs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('activities/documentation'));
    }

    public function test_update_rejects_documentation_when_existing_and_new_total_exceeds_three(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $officer = Officer::factory()->create();
        $activity = Activity::factory()->create(['type' => ActivityType::Internal]);

        foreach (range(1, 3) as $number) {
            Attachment::factory()->create([
                'attachable_type' => 'activity',
                'attachable_id' => $activity->id,
                'file_path' => "activities/documentation/lama-{$number}.jpg",
                'audience' => 'internal',
            ]);
        }

        $response = $this->from(route('admin.kegiatan.edit', $activity))
            ->actingAs($admin)
            ->put(route('admin.kegiatan.update', $activity), [
                ...$this->validPayload([$officer->id]),
                'current_updated_at' => $activity->updated_at->format('Y-m-d H:i:s'),
                'documentation' => [UploadedFile::fake()->image('keempat.jpg')],
            ]);

        $response->assertRedirect(route('admin.kegiatan.edit', $activity));
        $response->assertSessionHasErrors([
            'documentation' => 'Jumlah dokumentasi lama dan baru maksimal tiga foto.',
        ]);
        $this->assertDatabaseCount('attachments', 3);
        $this->assertDatabaseCount('audit_logs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('activities/documentation'));
    }

    public function test_external_and_soft_deleted_activities_do_not_use_internal_detail_endpoint(): void
    {
        $admin = User::factory()->create();
        $externalActivity = Activity::factory()->create(['type' => ActivityType::External]);
        $deletedActivity = Activity::factory()->create(['type' => ActivityType::Internal]);
        $deletedActivity->delete();

        $this->actingAs($admin)
            ->get(route('admin.kegiatan.show', $externalActivity))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('admin.kegiatan.show', $deletedActivity))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('admin.kegiatan.edit', $externalActivity))
            ->assertNotFound();
        $this->actingAs($admin)
            ->put(route('admin.kegiatan.update', $externalActivity), [
                ...$this->validPayload([Officer::factory()->create()->id]),
                'current_updated_at' => $externalActivity->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertNotFound();
    }

    public function test_admin_can_soft_delete_internal_activity_and_audit_the_action(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create([
            'type' => ActivityType::Internal,
            'name' => 'Kegiatan yang Dihapus',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.kegiatan.destroy', $activity));

        $response->assertRedirectToRoute('admin.kegiatan');
        $response->assertSessionHas('status', 'Kegiatan internal berhasil dihapus.');
        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => 'activity',
            'subject_id' => $activity->id,
            'action' => 'deleted',
            'description' => 'Kegiatan internal Kegiatan yang Dihapus dihapus.',
        ]);
    }

    public function test_external_activity_cannot_be_deleted_from_internal_endpoint(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::factory()->create(['type' => ActivityType::External]);

        $this->actingAs($admin)
            ->delete(route('admin.kegiatan.destroy', $activity))
            ->assertNotFound();

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'deleted_at' => null,
        ]);
    }

    public function test_guest_and_super_admin_cannot_create_view_or_update_admin_internal_activity(): void
    {
        $activity = Activity::factory()->create(['type' => ActivityType::Internal]);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->get(route('admin.kegiatan.create'))->assertRedirectToRoute('login');
        $this->post(route('admin.kegiatan.store'))->assertRedirectToRoute('login');
        $this->get(route('admin.kegiatan.show', $activity))->assertRedirectToRoute('login');
        $this->get(route('admin.kegiatan.edit', $activity))->assertRedirectToRoute('login');
        $this->put(route('admin.kegiatan.update', $activity))->assertRedirectToRoute('login');

        $this->actingAs($superAdmin)->get(route('admin.kegiatan.create'))->assertForbidden();
        $this->actingAs($superAdmin)->post(route('admin.kegiatan.store'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.kegiatan.show', $activity))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.kegiatan.edit', $activity))->assertForbidden();
        $this->actingAs($superAdmin)->put(route('admin.kegiatan.update', $activity))->assertForbidden();
    }

    /**
     * @param  list<int>  $officerIds
     * @return array<string, mixed>
     */
    private function validPayload(array $officerIds, ?int $templateId = null): array
    {
        $category = ActivityTemplate::query()->find($templateId)?->name
            ?? ActivityTemplate::factory()->create(['name' => 'Kategori Manual'])->name;

        return [
            'activity_template_id' => $templateId,
            'name' => 'Patroli Internal Terjadwal',
            'category' => $category,
            'activity_date' => now()->toDateString(),
            'start_time' => '08:30',
            'description' => 'Patroli rutin wilayah operasional.',
            'handling_result' => null,
            'location_address' => 'Kecamatan Wates, Kulon Progo',
            'latitude' => -7.8578000,
            'longitude' => 110.1589000,
            'officer_ids' => $officerIds,
        ];
    }
}
