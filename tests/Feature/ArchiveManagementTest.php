<?php

namespace Tests\Feature;

use App\ArchiveConfidentiality;
use App\Models\ActivityTemplate;
use App\Models\Archive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveManagementTest extends TestCase
{
    use RefreshDatabase;

    private ActivityTemplate $activityTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityTemplate = ActivityTemplate::factory()->create([
            'name' => 'Penertiban dan Patroli Wilayah',
            'is_active' => true,
        ]);
    }

    public function test_archive_page_lists_database_records_and_statistics(): void
    {
        $admin = User::factory()->create();
        $documentArchive = Archive::factory()->create([
            'title' => 'Dokumen Database',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public,
        ]);
        $photoArchive = Archive::factory()->create([
            'title' => 'Foto Database',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Confidential,
        ]);
        $documentArchive->files()->first()->update(['file_extension' => 'pdf']);
        $photoArchive->files()->first()->update(['file_extension' => 'jpg']);

        $response = $this->actingAs($admin)->get(route('admin.arsip'));

        $response->assertOk();
        $response->assertSeeText('Dokumen Database');
        $response->assertSeeText('Foto Database');
        $response->assertSeeText($this->activityTemplate->name);
        $response->assertSee('name="files[]"', false);
        $response->assertSee('multiple', false);
        $response->assertSee('id="archive-file-preview"', false);
        $response->assertSee('Hapus file terpilih', false);
        $response->assertDontSee('name="type"', false);
        $response->assertDontSeeText('BAP_Pasar_Senen.pdf');
        $response->assertViewHas('archiveSummary', [
            'total' => 2,
            'documents' => 1,
            'media' => 1,
            'confidential' => 1,
        ]);
    }

    public function test_admin_can_upload_an_archive(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $file = UploadedFile::fake()->create('sop-penertiban.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'SOP Penertiban',
            'operational_category' => $this->activityTemplate->name,
            'description' => 'Dokumen prosedur penertiban.',
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [$file],
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $response->assertSessionHas('status', 'Arsip berhasil diunggah.');
        $archive = Archive::query()->sole();
        $this->assertSame($admin->id, $archive->created_by_user_id);
        $this->assertSame($this->activityTemplate->name, $archive->operational_category);
        $archiveFile = $archive->files()->sole();
        $this->assertSame('sop-penertiban.pdf', $archiveFile->original_name);
        Storage::disk('local')->assertExists($archiveFile->file_path);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $archive->id,
            'module' => 'archives',
            'action' => 'created',
        ]);
    }

    public function test_admin_can_upload_three_archives_at_once(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Dokumentasi Kegiatan',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [
                UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->image('foto-satu.jpg'),
                UploadedFile::fake()->image('foto-dua.png'),
            ],
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $response->assertSessionHas('status', 'Arsip berhasil diunggah.');
        $this->assertDatabaseCount('archives', 1);
        $this->assertDatabaseCount('archive_files', 3);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertCount(3, Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_archive_folder_can_contain_pdf_image_and_spreadsheet_files(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Surat Bupati',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [
                UploadedFile::fake()->create('tandatangan.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->image('foto.jpg'),
                UploadedFile::fake()->create('keuangan.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $archive = Archive::query()->where('title', 'Surat Bupati')->sole();
        $this->assertEqualsCanonicalizing(
            ['tandatangan.pdf', 'foto.jpg', 'keuangan.xlsx'],
            $archive->files()->pluck('original_name')->all(),
        );
    }

    public function test_archive_upload_rejects_more_than_three_files(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Terlalu Banyak File',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.jpg'),
                UploadedFile::fake()->image('tiga.jpg'),
                UploadedFile::fake()->image('empat.jpg'),
            ],
        ]);

        $response->assertInvalid(['files']);
        $this->assertDatabaseCount('archives', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_archive_form_only_lists_active_activity_templates_and_rejects_inactive_category(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $inactiveTemplate = ActivityTemplate::factory()->inactive()->create([
            'name' => 'Template Kegiatan Nonaktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.arsip'))
            ->assertOk()
            ->assertSeeText($this->activityTemplate->name)
            ->assertDontSeeText($inactiveTemplate->name);

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Arsip Template Nonaktif',
            'operational_category' => $inactiveTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [UploadedFile::fake()->create('arsip.pdf', 100, 'application/pdf')],
        ]);

        $response->assertInvalid(['operational_category']);
        $this->assertDatabaseCount('archives', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_admin_can_upload_archive_larger_than_five_megabytes(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Arsip Besar',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [UploadedFile::fake()->create('arsip-besar.pdf', 6 * 1024, 'application/pdf')],
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $response->assertSessionHas('status', 'Arsip berhasil diunggah.');
        $this->assertDatabaseHas('archives', ['title' => 'Arsip Besar']);
        $this->assertDatabaseHas('archive_files', ['file_size' => 6 * 1024 * 1024]);
    }

    public function test_archive_larger_than_twenty_megabytes_is_rejected(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'Arsip Terlalu Besar',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [UploadedFile::fake()->create('arsip-terlalu-besar.pdf', 20 * 1024 + 1, 'application/pdf')],
        ]);

        $response->assertInvalid(['files.0']);
        $this->assertDatabaseCount('archives', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_admin_can_update_metadata_without_replacing_the_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/old.pdf', 'old document');
        $archive = Archive::factory()->create();
        $archive->files()->first()->update(['file_path' => 'archives/files/old.pdf', 'original_name' => 'old.pdf']);

        $response = $this->actingAs($admin)->put(route('admin.arsip.update', $archive), [
            'archive_context_id' => $archive->id,
            'title' => 'Judul Diperbarui',
            'operational_category' => $this->activityTemplate->name,
            'description' => 'Deskripsi baru.',
            'confidentiality' => ArchiveConfidentiality::Confidential->value,
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $this->assertSame('Judul Diperbarui', $archive->fresh()->title);
        $this->assertSame('archives/files/old.pdf', $archive->fresh()->files()->sole()->file_path);
        Storage::disk('local')->assertExists('archives/files/old.pdf');
    }

    public function test_editing_an_archive_can_add_a_file_without_removing_existing_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/old.pdf', 'old document');
        $archive = Archive::factory()->create();
        $archive->files()->first()->update(['file_path' => 'archives/files/old.pdf', 'original_name' => 'old.pdf']);

        $response = $this->actingAs($admin)->put(route('admin.arsip.update', $archive), [
            'archive_context_id' => $archive->id,
            'title' => $archive->title,
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => $archive->confidentiality->value,
            'files' => [UploadedFile::fake()->image('new-image.jpg')],
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $this->assertCount(2, $archive->fresh()->files);
        $newFile = $archive->fresh()->files()->where('original_name', 'new-image.jpg')->sole();
        Storage::disk('local')->assertExists($newFile->file_path);
        Storage::disk('local')->assertExists('archives/files/old.pdf');
    }

    public function test_archive_file_can_be_viewed_and_downloaded_securely(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/document.pdf', 'document content');
        $archive = Archive::factory()->create();
        $archiveFile = $archive->files()->first();
        $archiveFile->update(['file_path' => 'archives/files/document.pdf', 'original_name' => 'document.pdf', 'mime_type' => 'application/pdf']);

        $viewResponse = $this->actingAs($admin)->get(route('admin.arsip.show', [$archive, $archiveFile]));
        $downloadResponse = $this->actingAs($admin)->get(route('admin.arsip.download', [$archive, $archiveFile]));

        $viewResponse->assertOk();
        $viewResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $viewResponse->assertHeader('Content-Disposition', 'inline; filename=document.pdf');
        $downloadResponse->assertDownload('document.pdf');
    }

    public function test_invalid_archive_upload_is_rejected_without_storage_write(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.arsip.store'), [
            'title' => 'File Tidak Aman',
            'operational_category' => $this->activityTemplate->name,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'files' => [UploadedFile::fake()->create('script.php', 10, 'application/x-php')],
        ]);

        $response->assertInvalid(['files.0']);
        $this->assertDatabaseCount('archives', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_deleting_an_archive_uses_soft_delete_and_retains_its_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/retained.pdf', 'retained');
        $archive = Archive::factory()->create();
        $archive->files()->first()->update(['file_path' => 'archives/files/retained.pdf']);

        $response = $this->actingAs($admin)->delete(route('admin.arsip.destroy', $archive));

        $response->assertRedirectToRoute('admin.arsip');
        $response->assertSessionHas('status', 'Arsip berhasil dihapus.');
        $this->assertSoftDeleted($archive);
        Storage::disk('local')->assertExists('archives/files/retained.pdf');
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $archive->id,
            'module' => 'archives',
            'action' => 'deleted',
        ]);
    }
}
