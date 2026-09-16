<?php

namespace Database\Seeders;

use App\Models\User;
use App\ReportCategory;
use App\ReportSource;
use App\ReportSourcePlatform;
use App\ReportStatus;
use App\ReportType;
use App\UserRole;
use App\UserStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed a small, connected dataset for local development.
     */
    public function run(): void
    {
        $now = now()->startOfSecond();

        $this->seedPlaceholderFiles();

        DB::transaction(function () use ($now): void {
            $this->seedUsers($now);

            $adminId = (int) DB::table('users')->where('username', 'admin')->value('id');
            $superAdminId = (int) DB::table('users')->where('username', 'superadmin')->value('id');

            $this->seedGuestAccesses($now);
            $guestOneId = (int) DB::table('guest_accesses')->where('token_hash', hash('sha256', 'browser-warga-satu'))->value('id');
            $guestTwoId = (int) DB::table('guest_accesses')->where('token_hash', hash('sha256', 'browser-warga-dua'))->value('id');

            $this->seedReports($guestOneId, $guestTwoId, $now);
            $reportOneId = (int) DB::table('reports')->where('code', 'LAP-2026-0001')->value('id');
            $reportTwoId = (int) DB::table('reports')->where('code', 'LAP-2026-0002')->value('id');

            $this->seedReportHistories($adminId, $reportOneId, $reportTwoId, $now);
            $this->seedReportAttachments($reportOneId, $reportTwoId, $now);
            $this->seedActivityTemplates($now);
            $this->seedOrganizationAndActivities($adminId, $superAdminId, $reportOneId, $now);
            $this->seedArchivesAndAssets($adminId, $superAdminId, $now);
            $this->seedNotificationsAndAuditLogs($adminId, $superAdminId, $reportOneId, $reportTwoId, $now);
        });
    }

    private function seedPlaceholderFiles(): void
    {
        Storage::disk('local')->put('dummy/reports/laporan-pkl.txt', 'Dokumentasi contoh laporan penertiban PKL.');
        Storage::disk('local')->put('dummy/reports/laporan-sampah.txt', 'Dokumentasi contoh laporan tumpukan sampah.');
        Storage::disk('local')->put('dummy/archives/sop-penertiban.txt', 'Dokumen contoh SOP penertiban.');
        Storage::disk('local')->put('dummy/archives/laporan-patroli.txt', 'Dokumen contoh laporan patroli.');
    }

    private function seedUsers(CarbonInterface $now): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            ['name' => 'Admin Satpol PP', 'email' => 'admin@sipraja.test', 'email_verified_at' => $now, 'password' => Hash::make('password'), 'role' => UserRole::Admin->value, 'status' => UserStatus::Active->value, 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('users')->updateOrInsert(
            ['username' => 'superadmin'],
            ['name' => 'Super Admin Satpol PP', 'email' => 'superadmin@sipraja.test', 'email_verified_at' => $now, 'password' => Hash::make('password'), 'role' => UserRole::SuperAdmin->value, 'status' => UserStatus::Active->value, 'created_at' => $now, 'updated_at' => $now],
        );
    }

    private function seedGuestAccesses(CarbonInterface $now): void
    {
        foreach (['browser-warga-satu', 'browser-warga-dua'] as $daysAgo => $browserToken) {
            DB::table('guest_accesses')->updateOrInsert(
                ['token_hash' => hash('sha256', $browserToken)],
                ['last_used_at' => $now->copy()->subDays($daysAgo), 'expires_at' => $now->copy()->addYear(), 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    private function seedReports(int $guestOneId, int $guestTwoId, CarbonInterface $now): void
    {
        DB::table('reports')->updateOrInsert(
            ['code' => 'LAP-2026-0001'],
            [
                'guest_access_id' => $guestOneId,
                'created_by_user_id' => null,
                'type' => ReportType::Emergency->value,
                'category' => ReportCategory::PublicOrder->value,
                'source' => ReportSource::Online->value,
                'source_platform' => ReportSourcePlatform::Website->value,
                'reporter_name' => 'Budi Santoso',
                'reporter_phone' => '081234567890',
                'reporter_nik' => Crypt::encryptString('3401010101010001'),
                'description' => 'Pedagang kaki lima menutup akses trotoar dan mengganggu pengguna jalan.',
                'location_address' => 'Pasar Wates, Kabupaten Kulon Progo',
                'latitude' => -7.8578000,
                'longitude' => 110.1589000,
                'status' => ReportStatus::Completed->value,
                'completed_at' => $now->copy()->subDay(),
                'feedback_rating' => 5,
                'feedback_comment' => 'Penanganan cepat dan petugas sangat membantu.',
                'feedback_submitted_at' => $now->copy()->subHours(12),
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        );
        DB::table('reports')->updateOrInsert(
            ['code' => 'LAP-2026-0002'],
            [
                'guest_access_id' => $guestTwoId,
                'created_by_user_id' => null,
                'type' => ReportType::Information->value,
                'category' => ReportCategory::EnvironmentalCleanliness->value,
                'source' => ReportSource::Online->value,
                'source_platform' => ReportSourcePlatform::WhatsApp->value,
                'reporter_name' => 'Siti Rahma',
                'reporter_phone' => '081298765432',
                'reporter_nik' => Crypt::encryptString('3401010202020002'),
                'description' => 'Tumpukan sampah mengganggu ketertiban dan kebersihan area umum.',
                'location_address' => 'Alun-alun Wates, Kabupaten Kulon Progo',
                'latitude' => -7.8591000,
                'longitude' => 110.1603000,
                'status' => ReportStatus::Verifying->value,
                'completed_at' => null,
                'feedback_rating' => null,
                'feedback_comment' => null,
                'feedback_submitted_at' => null,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        );
    }

    private function seedReportHistories(int $adminId, int $reportOneId, int $reportTwoId, CarbonInterface $now): void
    {
        DB::table('report_status_histories')->updateOrInsert(
            ['report_id' => $reportOneId, 'status' => ReportStatus::Completed->value],
            ['changed_by_user_id' => $adminId, 'note' => 'Laporan selesai ditangani di lokasi.', 'created_at' => $now->copy()->subDay()],
        );
        DB::table('report_status_histories')->updateOrInsert(
            ['report_id' => $reportTwoId, 'status' => ReportStatus::Verifying->value],
            ['changed_by_user_id' => $adminId, 'note' => 'Petugas sedang memverifikasi kondisi lapangan.', 'created_at' => $now],
        );
    }

    private function seedReportAttachments(int $reportOneId, int $reportTwoId, CarbonInterface $now): void
    {
        DB::table('attachments')->updateOrInsert(
            ['file_path' => 'dummy/reports/laporan-pkl.txt'],
            ['attachable_type' => 'report', 'attachable_id' => $reportOneId, 'uploaded_by_user_id' => null, 'original_name' => 'laporan-pkl.txt', 'file_type' => 'document', 'mime_type' => 'text/plain', 'file_size' => Storage::disk('local')->size('dummy/reports/laporan-pkl.txt'), 'audience' => 'public', 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('attachments')->updateOrInsert(
            ['file_path' => 'dummy/reports/laporan-sampah.txt'],
            ['attachable_type' => 'report', 'attachable_id' => $reportTwoId, 'uploaded_by_user_id' => null, 'original_name' => 'laporan-sampah.txt', 'file_type' => 'document', 'mime_type' => 'text/plain', 'file_size' => Storage::disk('local')->size('dummy/reports/laporan-sampah.txt'), 'audience' => 'public', 'created_at' => $now, 'updated_at' => $now],
        );
    }

    private function seedActivityTemplates(CarbonInterface $now): void
    {
        DB::table('activity_templates')->updateOrInsert(
            ['name' => 'Patroli Ketertiban Umum, Ketentraman dan Perlindungan Masyarakat'],
            ['operational_category' => 'patroli_ketertiban_umum', 'default_description' => 'Patroli rutin untuk menjaga ketertiban dan ketenteraman umum.', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('activity_templates')->updateOrInsert(
            ['name' => 'Pelaksanaan Bantuan Pengamanan Kegiatan'],
            ['operational_category' => 'pengamanan_acara_kegiatan', 'default_description' => 'Pengamanan kegiatan masyarakat atau pemerintah daerah.', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        );
    }

    private function seedOrganizationAndActivities(int $adminId, int $superAdminId, int $reportOneId, CarbonInterface $now): void
    {
        DB::table('operational_zones')->updateOrInsert(
            ['code' => 'ZONA-WATES'],
            ['name' => 'Kecamatan Wates', 'description' => 'Wilayah operasional Kecamatan Wates.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('operational_zones')->updateOrInsert(
            ['code' => 'ZONA-PENGASIH'],
            ['name' => 'Kecamatan Pengasih', 'description' => 'Wilayah operasional Kecamatan Pengasih.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );

        $watesZoneId = (int) DB::table('operational_zones')->where('code', 'ZONA-WATES')->value('id');
        $pengasihZoneId = (int) DB::table('operational_zones')->where('code', 'ZONA-PENGASIH')->value('id');

        DB::table('organizational_units')->updateOrInsert(
            ['code' => 'REGU-A'],
            ['parent_id' => null, 'operational_zone_id' => $watesZoneId, 'leader_officer_id' => null, 'name' => 'Regu A - Patroli', 'type' => 'team', 'description' => 'Regu patroli wilayah Wates.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('organizational_units')->updateOrInsert(
            ['code' => 'REGU-B'],
            ['parent_id' => null, 'operational_zone_id' => $pengasihZoneId, 'leader_officer_id' => null, 'name' => 'Regu B - Patroli', 'type' => 'team', 'description' => 'Regu patroli wilayah Pengasih.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );

        $unitOneId = (int) DB::table('organizational_units')->where('code', 'REGU-A')->value('id');
        $unitTwoId = (int) DB::table('organizational_units')->where('code', 'REGU-B')->value('id');

        DB::table('officers')->updateOrInsert(
            ['employee_number' => 'PP-001'],
            ['user_id' => $adminId, 'organizational_unit_id' => $unitOneId, 'operational_zone_id' => $watesZoneId, 'name' => 'Ahmad Fauzi', 'position' => 'Kepala Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum', 'work_unit_name' => 'Seksi Pengendalian dan Operasional', 'unit_name' => 'Bidang Ketentraman dan Ketertiban Umum', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('officers')->updateOrInsert(
            ['employee_number' => 'PP-002'],
            ['user_id' => null, 'organizational_unit_id' => $unitTwoId, 'operational_zone_id' => $pengasihZoneId, 'name' => 'Siti Aminah', 'position' => 'Operator Layanan Operasional pada Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum', 'work_unit_name' => 'Seksi Perlindungan Masyarakat', 'unit_name' => 'Bidang Ketentraman dan Ketertiban Umum', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );

        $officerOneId = (int) DB::table('officers')->where('employee_number', 'PP-001')->value('id');
        $officerTwoId = (int) DB::table('officers')->where('employee_number', 'PP-002')->value('id');
        DB::table('organizational_units')->where('id', $unitOneId)->update(['leader_officer_id' => $officerOneId]);
        DB::table('organizational_units')->where('id', $unitTwoId)->update(['leader_officer_id' => $officerTwoId]);

        $patrolTemplateId = (int) DB::table('activity_templates')->where('sort_order', 1)->value('id');
        $securityTemplateId = (int) DB::table('activity_templates')->where('sort_order', 2)->value('id');

        DB::table('activities')->updateOrInsert(
            ['report_id' => $reportOneId],
            ['activity_template_id' => $patrolTemplateId, 'created_by_user_id' => $adminId, 'evaluated_by_user_id' => $superAdminId, 'name' => 'Penertiban PKL Pasar Wates', 'type' => 'external', 'category' => null, 'activity_date' => $now->copy()->subDay()->toDateString(), 'start_time' => '08:00:00', 'description' => 'Tindak lanjut laporan warga mengenai akses trotoar.', 'handling_result' => 'Akses trotoar kembali dapat digunakan oleh pejalan kaki.', 'location_address' => 'Pasar Wates, Kabupaten Kulon Progo', 'latitude' => -7.8578000, 'longitude' => 110.1589000, 'evaluation_notes' => 'Kegiatan berjalan tertib.', 'evaluation_obstacles' => 'Area pasar cukup ramai.', 'evaluated_at' => $now, 'created_at' => $now->copy()->subDay(), 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('activities')->updateOrInsert(
            ['name' => 'Patroli Rutin Kawasan Pengasih'],
            ['report_id' => null, 'activity_template_id' => $securityTemplateId, 'created_by_user_id' => $adminId, 'evaluated_by_user_id' => null, 'type' => 'internal', 'category' => 'Pelaksanaan Bantuan Pengamanan Kegiatan', 'activity_date' => $now->toDateString(), 'start_time' => '19:00:00', 'description' => 'Patroli rutin untuk memantau ketertiban fasilitas umum.', 'handling_result' => 'Situasi aman dan terkendali.', 'location_address' => 'Kecamatan Pengasih, Kabupaten Kulon Progo', 'latitude' => -7.8462000, 'longitude' => 110.1730000, 'evaluation_notes' => null, 'evaluation_obstacles' => null, 'evaluated_at' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );

        $externalActivityId = (int) DB::table('activities')->where('report_id', $reportOneId)->value('id');
        $internalActivityId = (int) DB::table('activities')->where('name', 'Patroli Rutin Kawasan Pengasih')->value('id');
        DB::table('activity_officer')->updateOrInsert(
            ['activity_id' => $externalActivityId, 'officer_id' => $officerOneId],
            ['assignment_role' => 'Koordinator Lapangan', 'notes' => 'Memimpin penanganan laporan.', 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('activity_officer')->updateOrInsert(
            ['activity_id' => $internalActivityId, 'officer_id' => $officerTwoId],
            ['assignment_role' => 'Anggota Patroli', 'notes' => 'Melaksanakan pemantauan wilayah.', 'created_at' => $now, 'updated_at' => $now],
        );
    }

    private function seedArchivesAndAssets(int $adminId, int $superAdminId, CarbonInterface $now): void
    {
        DB::table('archives')->updateOrInsert(
            ['title' => 'SOP Penertiban PKL'],
            ['created_by_user_id' => $adminId, 'operational_category' => 'Patroli Ketertiban Umum, Ketentraman dan Perlindungan Masyarakat', 'description' => 'Contoh dokumen prosedur penertiban pedagang kaki lima.', 'confidentiality' => 'public', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('archives')->updateOrInsert(
            ['title' => 'Laporan Patroli Bulanan'],
            ['created_by_user_id' => $superAdminId, 'operational_category' => 'Patroli Ketertiban Umum, Ketentraman dan Perlindungan Masyarakat', 'description' => 'Contoh rekap kegiatan patroli bulanan.', 'confidentiality' => 'confidential', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        $sopArchiveId = (int) DB::table('archives')->where('title', 'SOP Penertiban PKL')->value('id');
        $patrolArchiveId = (int) DB::table('archives')->where('title', 'Laporan Patroli Bulanan')->value('id');
        DB::table('archive_files')->updateOrInsert(
            ['archive_id' => $sopArchiveId, 'original_name' => 'sop-penertiban.txt'],
            ['file_path' => 'dummy/archives/sop-penertiban.txt', 'mime_type' => 'text/plain', 'file_extension' => 'txt', 'file_size' => Storage::disk('local')->size('dummy/archives/sop-penertiban.txt'), 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('archive_files')->updateOrInsert(
            ['archive_id' => $patrolArchiveId, 'original_name' => 'laporan-patroli.txt'],
            ['file_path' => 'dummy/archives/laporan-patroli.txt', 'mime_type' => 'text/plain', 'file_extension' => 'txt', 'file_size' => Storage::disk('local')->size('dummy/archives/laporan-patroli.txt'), 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('assets')->updateOrInsert(
            ['asset_code' => 'AST-001'],
            ['created_by_user_id' => $adminId, 'name' => 'Mobil Patroli Dalmas', 'category' => 'vehicle', 'condition' => 'good', 'location' => 'Kantor Satpol PP', 'acquisition_year' => 2022, 'tax_due_date' => '2027-03-15', 'next_oil_service_km' => 45000, 'last_oil_service_date' => $now->copy()->subMonths(4)->toDateString(), 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
        DB::table('assets')->updateOrInsert(
            ['asset_code' => 'AST-002'],
            ['created_by_user_id' => $adminId, 'name' => 'Tenda Penertiban', 'category' => 'equipment', 'condition' => 'good', 'location' => 'Gudang Satpol PP', 'acquisition_year' => 2023, 'tax_due_date' => null, 'next_oil_service_km' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        );
    }

    private function seedNotificationsAndAuditLogs(int $adminId, int $superAdminId, int $reportOneId, int $reportTwoId, CarbonInterface $now): void
    {
        DB::table('notifications')->updateOrInsert(
            ['id' => '10000000-0000-4000-8000-000000000001'],
            ['type' => 'App\\Notifications\\NewReportNotification', 'notifiable_type' => User::class, 'notifiable_id' => $adminId, 'data' => json_encode(['title' => 'Laporan baru', 'message' => 'Laporan LAP-2026-0002 menunggu verifikasi.', 'report_id' => $reportTwoId], JSON_THROW_ON_ERROR), 'read_at' => null, 'created_at' => $now, 'updated_at' => $now],
        );
        DB::table('notifications')->updateOrInsert(
            ['id' => '10000000-0000-4000-8000-000000000002'],
            ['type' => 'App\\Notifications\\ReportCompletedNotification', 'notifiable_type' => User::class, 'notifiable_id' => $superAdminId, 'data' => json_encode(['title' => 'Laporan selesai', 'message' => 'Laporan LAP-2026-0001 telah ditangani.', 'report_id' => $reportOneId], JSON_THROW_ON_ERROR), 'read_at' => $now, 'created_at' => $now->copy()->subDay(), 'updated_at' => $now],
        );
        DB::table('audit_logs')->updateOrInsert(
            ['module' => 'reports', 'action' => 'completed', 'subject_type' => 'report', 'subject_id' => $reportOneId],
            ['actor_user_id' => $adminId, 'description' => 'Admin menyelesaikan laporan LAP-2026-0001.', 'old_values' => json_encode(['status' => 'in_progress'], JSON_THROW_ON_ERROR), 'new_values' => json_encode(['status' => 'completed'], JSON_THROW_ON_ERROR), 'ip_address' => '127.0.0.1', 'user_agent' => 'Development Seeder', 'created_at' => $now->copy()->subDay()],
        );
        DB::table('audit_logs')->updateOrInsert(
            ['module' => 'reports', 'action' => 'verified', 'subject_type' => 'report', 'subject_id' => $reportTwoId],
            ['actor_user_id' => $adminId, 'description' => 'Admin memulai verifikasi laporan LAP-2026-0002.', 'old_values' => json_encode(['status' => 'received'], JSON_THROW_ON_ERROR), 'new_values' => json_encode(['status' => 'verifying'], JSON_THROW_ON_ERROR), 'ip_address' => '127.0.0.1', 'user_agent' => 'Development Seeder', 'created_at' => $now],
        );
    }
}
