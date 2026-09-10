<?php

namespace Tests\Feature;

use App\Models\Officer;
use App\Models\OperationalZone;
use App\Models\OrganizationalUnit;
use App\Models\User;
use App\OfficerStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_form_uses_a_phone_specific_input(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get(route('super_admin.petugas'))
            ->assertOk()
            ->assertSee('name="phone"', false)
            ->assertSee('type="tel"', false)
            ->assertSee('inputmode="tel"', false)
            ->assertDontSee('function getPetugas()', false)
            ->assertDontSee('function saveAll()', false);
    }

    public function test_super_admin_can_create_officer_with_unit_and_zone(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $unit = OrganizationalUnit::factory()->create();
        $zone = OperationalZone::factory()->create();

        $this->actingAs($superAdmin)->post(route('super_admin.petugas.store'), [
            'name' => 'Petugas Database',
            'employee_number' => 'PP-DB-001',
            'rank' => 'Penata Muda',
            'position' => 'Anggota',
            'organizational_unit_id' => $unit->id,
            'operational_zone_id' => $zone->id,
            'email' => 'petugas@example.test',
            'phone' => '081234567890',
            'address' => 'Wates',
            'joined_at' => now()->toDateString(),
            'status' => OfficerStatus::Active->value,
        ])->assertRedirect(route('super_admin.petugas'));

        $this->assertDatabaseHas('officers', [
            'employee_number' => 'PP-DB-001',
            'organizational_unit_id' => $unit->id,
            'operational_zone_id' => $zone->id,
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
            'rank' => 'Penata',
            'position' => 'Komandan Regu',
            'organizational_unit_id' => null,
            'operational_zone_id' => null,
            'email' => 'petugas.baru@example.test',
            'phone' => '0812-3456-7890',
            'address' => 'Wates',
            'joined_at' => now()->toDateString(),
            'status' => OfficerStatus::Active->value,
        ])->assertRedirect(route('super_admin.petugas'));

        $this->assertDatabaseHas('officers', ['id' => $officer->id, 'name' => 'Nama Baru', 'phone' => '0812-3456-7890']);
    }

    public function test_officer_rejects_invalid_email_phone_and_future_join_date(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('super_admin.petugas.store'), [
            'name' => 'Petugas Invalid',
            'employee_number' => 'INVALID-001',
            'email' => 'bukan-email',
            'phone' => 'nomor-hp',
            'joined_at' => now()->addDay()->toDateString(),
            'status' => OfficerStatus::Active->value,
        ])->assertSessionHasErrors(['email', 'phone', 'joined_at']);

        $this->assertDatabaseMissing('officers', ['employee_number' => 'INVALID-001']);
    }
}
