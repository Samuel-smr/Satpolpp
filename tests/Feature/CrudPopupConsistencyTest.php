<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudPopupConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_renders_success_and_error_feedback_through_global_popup(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->withSession([
                'status' => 'Operasi berhasil.',
                'error' => 'Operasi gagal.',
            ])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('window.tampilToast("Operasi berhasil.", \'success\')', false)
            ->assertSee('window.tampilToast("Operasi gagal.", \'error\')', false)
            ->assertSee('event.defaultPrevented', false)
            ->assertSee("button.classList.add('cursor-wait', 'opacity-70')", false);
    }

    public function test_super_admin_layout_renders_feedback_and_shared_confirmation_helpers(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->withSession([
                'status' => 'Data berhasil disimpan.',
                'error' => 'Data gagal disimpan.',
            ])
            ->get(route('super_admin.dashboard'));

        $response->assertOk()
            ->assertSee('window.tampilToast("Data berhasil disimpan.", \'success\')', false)
            ->assertSee('window.tampilToast("Data gagal disimpan.", \'error\')', false)
            ->assertSee('window.tampilkanToastLaluMuatUlang', false)
            ->assertSee('form[data-confirm-delete]', false)
            ->assertSee('confirmText', false)
            ->assertSee('event.defaultPrevented', false);
    }

    public function test_report_delete_uses_application_confirmation_modal_instead_of_browser_confirm(): void
    {
        $admin = User::factory()->create();
        $report = Report::factory()->assignedTo($admin)->create();

        $response = $this->actingAs($admin)->get(route('admin.pengaduan.detail', $report));

        $response->assertOk()
            ->assertSee('data-confirm-delete="Hapus laporan ini?', false)
            ->assertDontSee('return confirm(', false);
    }
}
