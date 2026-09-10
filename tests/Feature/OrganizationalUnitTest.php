<?php

namespace Tests\Feature;

use App\Models\OperationalZone;
use App\Models\OrganizationalUnit;
use App\Models\User;
use App\OrganizationalUnitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationalUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_view_organizational_unit(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $zone = OperationalZone::factory()->create();

        $this->actingAs($superAdmin)->post(route('super_admin.organisasi.store'), [
            'name' => 'Regu Database',
            'code' => 'REGU-DB',
            'type' => OrganizationalUnitType::Team->value,
            'operational_zone_id' => $zone->id,
            'leader_officer_id' => null,
            'parent_id' => null,
            'description' => 'Regu hasil integrasi database.',
            'is_active' => true,
        ])->assertRedirect(route('super_admin.organisasi'));

        $this->assertDatabaseHas('organizational_units', ['code' => 'REGU-DB']);
        $this->actingAs($superAdmin)->get(route('super_admin.organisasi'))
            ->assertOk()
            ->assertSee('Regu Database')
            ->assertSee($zone->name)
            ->assertDontSee('function getUnits()', false)
            ->assertDontSee('function saveAll()', false);
    }

    public function test_unit_with_children_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $parent = OrganizationalUnit::factory()->create();
        OrganizationalUnit::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($superAdmin)->deleteJson(route('super_admin.organisasi.destroy', $parent))
            ->assertUnprocessable();

        $this->assertNotSoftDeleted($parent);
    }

    public function test_super_admin_can_update_an_organizational_unit(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $unit = OrganizationalUnit::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($superAdmin)->put(route('super_admin.organisasi.update', $unit), [
            'name' => 'Nama Baru',
            'code' => 'UNIT-BARU',
            'type' => OrganizationalUnitType::Team->value,
            'parent_id' => null,
            'operational_zone_id' => null,
            'leader_officer_id' => null,
            'description' => 'Diperbarui melalui CRUD.',
            'is_active' => true,
        ])->assertRedirect(route('super_admin.organisasi'));

        $this->assertDatabaseHas('organizational_units', ['id' => $unit->id, 'name' => 'Nama Baru', 'code' => 'UNIT-BARU']);
    }

    public function test_unit_cannot_select_its_descendant_as_parent(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $parent = OrganizationalUnit::factory()->create();
        $child = OrganizationalUnit::factory()->create(['parent_id' => $parent->id]);
        $grandchild = OrganizationalUnit::factory()->create(['parent_id' => $child->id]);

        $this->actingAs($superAdmin)
            ->from(route('super_admin.organisasi'))
            ->put(route('super_admin.organisasi.update', $parent), [
                'name' => $parent->name,
                'code' => $parent->code,
                'type' => $parent->type->value,
                'parent_id' => $grandchild->id,
                'operational_zone_id' => null,
                'leader_officer_id' => null,
                'description' => null,
                'is_active' => true,
            ])
            ->assertRedirect(route('super_admin.organisasi'))
            ->assertSessionHasErrors(['parent_id' => 'Unit induk tidak boleh berasal dari unit bawahannya sendiri.']);

        $this->assertNull($parent->fresh()->parent_id);
    }
}
