<?php

namespace Tests\Feature;

use App\ArchiveConfidentiality;
use App\Models\Archive;
use App\Models\User;
use App\OperationalCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_page_lists_database_records_and_statistics(): void
    {
        $admin = User::factory()->create();
        Archive::factory()->create([
            'title' => 'Dokumen Database',
            'file_extension' => 'pdf',
            'confidentiality' => ArchiveConfidentiality::Public,
        ]);
        Archive::factory()->create([
            'title' => 'Foto Database',
            'file_extension' => 'jpg',
            'confidentiality' => ArchiveConfidentiality::Confidential,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.arsip'));

        $response->assertOk();
        $response->assertSeeText('Dokumen Database');
        $response->assertSeeText('Foto Database');
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
            'operational_category' => OperationalCategory::RegionalRegulationCompliance->value,
            'description' => 'Dokumen prosedur penertiban.',
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'file' => $file,
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $response->assertSessionHas('status', 'Arsip berhasil diunggah.');
        $archive = Archive::query()->sole();
        $this->assertSame($admin->id, $archive->created_by_user_id);
        $this->assertSame('sop-penertiban.pdf', $archive->original_name);
        Storage::disk('local')->assertExists($archive->file_path);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $archive->id,
            'module' => 'archives',
            'action' => 'created',
        ]);
    }

    public function test_admin_can_update_metadata_without_replacing_the_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/old.pdf', 'old document');
        $archive = Archive::factory()->create([
            'file_path' => 'archives/files/old.pdf',
            'original_name' => 'old.pdf',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.arsip.update', $archive), [
            'archive_context_id' => $archive->id,
            'title' => 'Judul Diperbarui',
            'operational_category' => OperationalCategory::PublicOrderPatrol->value,
            'description' => 'Deskripsi baru.',
            'confidentiality' => ArchiveConfidentiality::Confidential->value,
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $this->assertSame('Judul Diperbarui', $archive->fresh()->title);
        $this->assertSame('archives/files/old.pdf', $archive->fresh()->file_path);
        Storage::disk('local')->assertExists('archives/files/old.pdf');
    }

    public function test_replacing_an_archive_file_removes_the_old_file_after_update(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/old.pdf', 'old document');
        $archive = Archive::factory()->create([
            'file_path' => 'archives/files/old.pdf',
            'original_name' => 'old.pdf',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.arsip.update', $archive), [
            'archive_context_id' => $archive->id,
            'title' => $archive->title,
            'operational_category' => $archive->operational_category->value,
            'confidentiality' => $archive->confidentiality->value,
            'file' => UploadedFile::fake()->image('new-image.jpg'),
        ]);

        $response->assertRedirectToRoute('admin.arsip');
        $updatedArchive = $archive->fresh();
        $this->assertSame('new-image.jpg', $updatedArchive->original_name);
        Storage::disk('local')->assertExists($updatedArchive->file_path);
        Storage::disk('local')->assertMissing('archives/files/old.pdf');
    }

    public function test_archive_file_can_be_viewed_and_downloaded_securely(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/document.pdf', 'document content');
        $archive = Archive::factory()->create([
            'file_path' => 'archives/files/document.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $viewResponse = $this->actingAs($admin)->get(route('admin.arsip.show', $archive));
        $downloadResponse = $this->actingAs($admin)->get(route('admin.arsip.download', $archive));

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
            'operational_category' => OperationalCategory::PublicOrderPatrol->value,
            'confidentiality' => ArchiveConfidentiality::Public->value,
            'file' => UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
        ]);

        $response->assertInvalid(['file']);
        $this->assertDatabaseCount('archives', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('archives/files'));
    }

    public function test_deleting_an_archive_uses_soft_delete_and_retains_its_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Storage::disk('local')->put('archives/files/retained.pdf', 'retained');
        $archive = Archive::factory()->create(['file_path' => 'archives/files/retained.pdf']);

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
