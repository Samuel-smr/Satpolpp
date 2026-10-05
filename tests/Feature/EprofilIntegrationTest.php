<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityPersonnel;
use App\Models\ActivityTemplate;
use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use App\Services\EprofilService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EprofilIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://eprofil.example.test/pegawai';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.eprofil', ['url' => self::URL, 'token' => 'test-token']);
        config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
    }

    /** @return array<string, array{string, bool}> */
    public static function directoryPages(): array
    {
        return [
            'admin create' => ['admin.kegiatan.create', false],
            'super admin create' => ['super_admin.kegiatan.create', true],
            'admin edit' => ['admin.kegiatan.edit', false],
            'super admin edit' => ['super_admin.kegiatan.edit', true],
            'completion' => ['admin.pengaduan.detail', false],
            'management' => ['super_admin.petugas', true],
        ];
    }

    #[DataProvider('directoryPages')]
    public function test_requested_pages_display_api_employees_and_exclude_manual_officers(string $routeName, bool $isSuperAdmin): void
    {
        $this->fakeDirectory();
        $user = $isSuperAdmin ? User::factory()->superAdmin()->create() : User::factory()->create();
        $parameters = match ($routeName) {
            'admin.kegiatan.edit', 'super_admin.kegiatan.edit' => [Activity::factory()->create()],
            'admin.pengaduan.detail' => [Report::factory()->create(['status' => ReportStatus::InProgress])],
            default => [],
        };

        $response = $this->actingAs($user)->get(route($routeName, $parameters));

        $response->assertOk()->assertSeeText('SARTONO, S.Sos.')->assertSeeText('197202121991011001')
            ->assertSeeText('Kepala Seksi Perlindungan Masyarakat')->assertDontSeeText('Petugas Lokal Saja')->assertDontSee('test-token');
        Http::assertSent(fn ($request): bool => $request->url() === self::URL
            && $request->method() === 'GET' && $request->hasHeader('Authorization', 'Bearer test-token'));
        if ($routeName === 'super_admin.petugas') {
            $response->assertSeeText('e-Profil')->assertDontSee('onclick="bukaTambah()"', false);
        }
    }

    public function test_api_reads_preserve_nip_assignments_without_storing_employee_details(): void
    {
        $this->fakeDirectory();
        $activity = Activity::factory()->create();
        $activity->personnel()->create(['employee_nip' => '197202121991011001']);
        $service = app(EprofilService::class);

        $service->officers();
        $service->officers();

        $this->assertSame('SARTONO, S.Sos.', $activity->officers->sole()->name);
        $this->assertSame('197202121991011001', $activity->personnel()->sole()->employee_nip);
        $this->assertFalse(Schema::hasTable('officers'));
        $this->assertFalse(Schema::hasTable('activity_officer'));
        $this->assertFalse(Schema::hasColumn('activity_personnel', 'name'));
        $this->assertFalse(Schema::hasColumn('activity_personnel', 'position'));
        $cacheKey = 'eprofil:employees:'.hash('sha256', self::URL.'|test-token');
        $this->assertNull(Cache::get($cacheKey));
        $this->assertNull(Cache::get($cacheKey.':last-success'));
        Http::assertSentCount(1);
    }

    public function test_unavailable_api_does_not_reuse_employee_details_from_previous_request(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::URL => Http::sequence()->push($this->payload())->pushStatus(503)->pushStatus(503)]);
        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))->assertOk()->assertSeeText('SARTONO, S.Sos.');

        $this->get(route('super_admin.petugas'))->assertOk()->assertDontSeeText('SARTONO, S.Sos.')
            ->assertSeeText('Data pegawai e-Profil belum dapat dimuat.');

        $this->assertFalse(Schema::hasTable('officers'));
        Http::assertSentCount(3);
    }

    public function test_failed_first_request_does_not_present_local_officers_as_api_data(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::URL => Http::response(['success' => false], 401)]);

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))
            ->assertOk()->assertSeeText('Data pegawai e-Profil belum dapat dimuat.')->assertDontSeeText('Petugas Lokal Saja');

        $this->assertFalse(Schema::hasTable('officers'));
        Http::assertSentCount(1);
    }

    public function test_invalid_api_row_prevents_partial_sync(): void
    {
        Http::preventStrayRequests();
        $payload = $this->payload();
        $payload['data'][1]['nip'] = 196903181996031003;
        Http::fake([self::URL => Http::response($payload)]);

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))
            ->assertOk()->assertSeeText('Data pegawai e-Profil belum dapat dimuat.');

        $this->assertFalse(Schema::hasTable('officers'));
        Http::assertSentCount(1);
    }

    public function test_employee_write_endpoints_are_removed(): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post('/super-admin/petugas', [])->assertStatus(405);
        $this->put('/super-admin/petugas/197202121991011001', ['name' => 'Nama Baru'])->assertNotFound();
        $this->deleteJson('/super-admin/petugas/197202121991011001')->assertNotFound();

        $this->assertFalse(Schema::hasTable('officers'));
        Http::assertNothingSent();
    }

    public function test_unauthorized_requests_do_not_call_employee_api(): void
    {
        Http::preventStrayRequests();

        $this->get(route('admin.kegiatan.create'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('super_admin.petugas'))->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_missing_token_does_not_send_unauthenticated_request(): void
    {
        Http::preventStrayRequests();
        config()->set('services.eprofil.token', '');

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))
            ->assertOk()->assertSeeText('Data pegawai e-Profil belum dapat dimuat.');

        Http::assertNothingSent();
        $this->assertFalse(Schema::hasTable('officers'));
    }

    public function test_api_employee_can_be_assigned_to_an_activity_and_retained_on_edit(): void
    {
        $this->fakeDirectory();
        $this->actingAs(User::factory()->create())->get(route('admin.kegiatan.create'))->assertOk();
        $officer = app(EprofilService::class)->officers()->firstWhere('employee_number', '197202121991011001');
        $payload = $this->activityPayload($officer->employee_number);

        $this->post(route('admin.kegiatan.store'), $payload)->assertSessionHasNoErrors();
        $activity = Activity::query()->sole();
        $this->assertSame($officer->employee_number, $activity->personnel()->sole()->employee_nip);
        $this->put(route('admin.kegiatan.update', $activity), [
            ...$payload,
            'current_updated_at' => $activity->updated_at->format('Y-m-d H:i:s'),
        ])->assertRedirectToRoute('admin.kegiatan.show', $activity)->assertSessionHasNoErrors();
        $this->assertSame($officer->employee_number, $activity->fresh()->personnel()->sole()->employee_nip);
        Http::assertSentCount(3);
    }

    public function test_nip_outside_api_directory_cannot_be_assigned(): void
    {
        $this->fakeDirectory();

        $this->actingAs(User::factory()->create())->post(route('admin.kegiatan.store'), $this->activityPayload('000000000000000001'))
            ->assertInvalid(['employee_nips.0']);

        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('activity_personnel', 0);
        Http::assertSentCount(1);
    }

    public function test_report_can_be_completed_with_an_api_employee(): void
    {
        Storage::fake('local');
        $this->fakeDirectory();
        $report = Report::factory()->create(['status' => ReportStatus::InProgress]);
        $this->actingAs(User::factory()->create())->get(route('admin.pengaduan.detail', $report))->assertOk();
        $officer = app(EprofilService::class)->officers()->firstWhere('employee_number', '197202121991011001');

        $this->post(route('admin.pengaduan.complete.store', $report), [
            ...$this->activityPayload($officer->employee_number),
            'current_status' => ReportStatus::InProgress->value,
            'handling_result' => 'Lokasi telah tertib.',
            'evidence' => [UploadedFile::fake()->image('bukti.jpg')],
        ])->assertRedirectToRoute('admin.pengaduan.detail', $report)->assertSessionHasNoErrors();

        $this->assertSame(ReportStatus::Completed, $report->fresh()->status);
        $this->assertSame($officer->employee_number, $report->fresh()->activity->personnel()->sole()->employee_nip);
        Http::assertSent(fn ($request): bool => $request->url() === self::URL);
    }

    public function test_api_array_reordering_does_not_change_the_assigned_employee(): void
    {
        Http::preventStrayRequests();
        $payload = $this->payload();
        $reordered = ['success' => true, 'data' => array_reverse($payload['data'])];
        Http::fake([self::URL => Http::sequence()->push($payload)->push($reordered)]);
        $activity = Activity::factory()->create();
        ActivityPersonnel::factory()->for($activity)->create(['employee_nip' => '197202121991011001']);
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.kegiatan.show', $activity))->assertOk()->assertSeeText('SARTONO, S.Sos.')->assertDontSeeText('BUDI HARTONO');
        $this->get(route('admin.kegiatan.show', $activity))->assertOk()->assertSeeText('SARTONO, S.Sos.')->assertDontSeeText('BUDI HARTONO');

        $this->assertSame('197202121991011001', $activity->personnel()->sole()->employee_nip);
        Http::assertSentCount(2);
    }

    public function test_edit_keeps_existing_nip_when_employee_is_no_longer_in_api(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::URL => Http::response(['success' => true, 'data' => []])]);
        $activity = Activity::factory()->create();
        ActivityPersonnel::factory()->for($activity)->create(['employee_nip' => '197202121991011001', 'notes' => 'Penugasan tetap.']);
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.kegiatan.edit', $activity))->assertOk()->assertSeeText('Pegawai tidak tersedia')
            ->assertSee('value="197202121991011001" checked', false);
        $this->put(route('admin.kegiatan.update', $activity), [
            ...$this->activityPayload('197202121991011001'),
            'current_updated_at' => $activity->updated_at->format('Y-m-d H:i:s'),
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.kegiatan.show', $activity);

        $this->assertSame('197202121991011001', $activity->fresh()->personnel()->sole()->employee_nip);
        $this->assertSame('Penugasan tetap.', $activity->fresh()->personnel()->sole()->notes);
        Http::assertSentCount(2);
    }

    public function test_numeric_nip_is_rejected_without_losing_precision(): void
    {
        $this->fakeDirectory();

        $this->actingAs(User::factory()->create())->post(route('admin.kegiatan.store'), [
            ...$this->activityPayload('197202121991011001'),
            'employee_nips' => [197202121991011001],
        ])->assertInvalid(['employee_nips.0']);

        $this->assertDatabaseCount('activities', 0);
        Http::assertSentCount(1);
    }

    private function fakeDirectory(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::URL => Http::response($this->payload()),
            'http://localhost:3456/send' => Http::response(['status' => true]),
        ]);
    }

    /** @return array{success: bool, data: list<array{nama: string, nip: string, jabatan_kode: string, jabatan_nama: string}>} */
    private function payload(): array
    {
        return ['success' => true, 'data' => [
            ['nama' => 'SARTONO, S.Sos.', 'nip' => '197202121991011001', 'jabatan_kode' => '523000300000310341000208', 'jabatan_nama' => 'Kepala Seksi Perlindungan Masyarakat'],
            ['nama' => 'BUDI HARTONO, S.Si., M.Si.', 'nip' => '196903181996031003', 'jabatan_kode' => '523000100000110122000608', 'jabatan_nama' => 'Kepala Satuan Polisi Pamong Praja'],
        ]];
    }

    /** @return array<string, mixed> */
    private function activityPayload(string $employeeNip): array
    {
        $template = ActivityTemplate::factory()->create();

        return [
            'activity_template_id' => $template->id,
            'name' => 'Patroli rutin',
            'category' => $template->name,
            'activity_date' => now()->toDateString(),
            'location_address' => 'Wates',
            'latitude' => '-7.8581',
            'longitude' => '110.1572',
            'employee_nips' => [$employeeNip],
        ];
    }
}
