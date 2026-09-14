<?php

namespace Tests\Feature;

use App\Models\Officer;
use App\Models\User;
use App\OfficerStatus;
use App\Support\OfficerOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_form_only_shows_requested_personnel_fields(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get(route('super_admin.petugas'))
            ->assertOk()
            ->assertSee('name="employee_number"', false)
            ->assertSee('id="petugas-form" method="POST"', false)
            ->assertSee('name="position"', false)
            ->assertSee('name="work_unit_name"', false)
            ->assertSee('name="unit_name"', false)
            ->assertSee('window.tutupModal = function', false)
            ->assertSee('onclick="tutupModal()"', false)
            ->assertDontSee('name="phone"', false)
            ->assertDontSee('name="rank"', false)
            ->assertDontSee('function getPetugas()', false)
            ->assertDontSee('function saveAll()', false);
    }

    public function test_super_admin_can_create_officer_with_work_structure(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $position = OfficerOptions::positions()[0];
        $workUnit = OfficerOptions::workUnits()[0];
        $unit = OfficerOptions::units()[0];

        $this->actingAs($superAdmin)->post(route('super_admin.petugas.store'), [
            'name' => 'Petugas Database',
            'employee_number' => 'PP-DB-001',
            'position' => $position,
            'work_unit_name' => $workUnit,
            'unit_name' => $unit,
        ])->assertRedirect(route('super_admin.petugas'));

        $this->assertDatabaseHas('officers', [
            'employee_number' => 'PP-DB-001',
            'position' => $position,
            'work_unit_name' => $workUnit,
            'unit_name' => $unit,
            'status' => OfficerStatus::Active->value,
        ]);
    }

    public function test_status_cycles_and_delete_uses_soft_delete(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = Officer::factory()->create(['status' => OfficerStatus::Active]);

        $this->actingAs($superAdmin)->patchJson(route('super_admin.petugas.status', $officer))
            ->assertOk()
            ->assertJsonPath('status', OfficerStatus::Leave->value);
        $this->actingAs($superAdmin)->deleteJson(route('super_admin.petugas.destroy', $officer))->assertOk();

        $this->assertSoftDeleted($officer);
    }

    public function test_super_admin_can_update_an_officer(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $officer = Officer::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($superAdmin)->put(route('super_admin.petugas.update', $officer), [
            'name' => 'Nama Baru',
            'employee_number' => $officer->employee_number,
            'position' => OfficerOptions::positions()[1],
            'work_unit_name' => OfficerOptions::workUnits()[1],
            'unit_name' => OfficerOptions::units()[1],
        ])->assertRedirect(route('super_admin.petugas'));

        $this->assertDatabaseHas('officers', ['id' => $officer->id, 'name' => 'Nama Baru', 'work_unit_name' => OfficerOptions::workUnits()[1]]);
    }

    public function test_officer_rejects_values_outside_the_official_options(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('super_admin.petugas.store'), [
            'name' => 'Petugas Invalid',
            'employee_number' => 'INVALID-001',
            'position' => 'Jabatan Tidak Resmi',
            'work_unit_name' => 'Satuan Tidak Resmi',
            'unit_name' => 'Unit Tidak Resmi',
        ])->assertSessionHasErrors(['position', 'work_unit_name', 'unit_name']);

        $this->assertDatabaseMissing('officers', ['employee_number' => 'INVALID-001']);
    }
}
