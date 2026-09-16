<?php

namespace Tests\Feature;

use App\AssetCategory;
use App\AssetCondition;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_asset_page_uses_database_records_and_statistics(): void
    {
        $admin = User::factory()->create();
        Asset::factory()->create([
            'name' => 'Mobil Database',
            'category' => AssetCategory::Vehicle,
            'condition' => AssetCondition::Good,
        ]);
        Asset::factory()->create([
            'name' => 'Radio Database',
            'category' => AssetCategory::Equipment,
            'condition' => AssetCondition::NeedsRepair,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.aset'));

        $response->assertOk();
        $response->assertSeeText('Mobil Database');
        $response->assertSeeText('Radio Database');
        $response->assertSeeText('Data Database');
        $response->assertSee('id="asset-filter-category"', false);
        $response->assertSee('id="asset-category" name="category" required', false);
        $response->assertSee("const kategoriAset = document.getElementById('asset-category');", false);
        $response->assertSee('id="kendaraan-fields"', false);
        $response->assertDontSeeText('Mobil Pick Up Operasional');
        $response->assertViewHas('assetSummary', [
            'total' => 2,
            'good' => 1,
            'needs_attention' => 1,
            'vehicles' => 1,
        ]);
    }

    public function test_admin_can_create_vehicle_asset_with_maintenance_fields(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.aset.store'), [
            'asset_code' => 'AST-DB-001',
            'name' => 'Mobil Patroli Baru',
            'category' => AssetCategory::Vehicle->value,
            'condition' => AssetCondition::Good->value,
            'location' => 'Kantor Satpol PP',
            'acquisition_year' => 2026,
            'tax_due_date' => '2027-09-09',
            'next_oil_service_km' => 15000,
            'last_oil_service_date' => '2026-06-09',
        ]);

        $response->assertRedirectToRoute('admin.aset');
        $response->assertSessionHas('status', 'Aset berhasil ditambahkan.');
        $this->assertDatabaseHas('assets', [
            'created_by_user_id' => $admin->id,
            'asset_code' => 'AST-DB-001',
            'name' => 'Mobil Patroli Baru',
            'category' => AssetCategory::Vehicle->value,
            'next_oil_service_km' => 15000,
        ]);
        $this->assertSame('2027-09-09', Asset::query()->sole()->tax_due_date->toDateString());
        $this->assertSame('2026-06-09', Asset::query()->sole()->last_oil_service_date->toDateString());
        $this->assertSame('2027-09-09', Asset::query()->sole()->tax_due_date->toDateString());
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'assets',
            'action' => 'created',
        ]);
    }

    public function test_non_vehicle_asset_does_not_store_vehicle_only_fields(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.aset.store'), [
            'name' => 'Radio Komunikasi',
            'category' => AssetCategory::Equipment->value,
            'condition' => AssetCondition::Good->value,
            'tax_due_date' => '2027-09-09',
            'next_oil_service_km' => 15000,
            'last_oil_service_date' => '2026-06-09',
        ])->assertRedirectToRoute('admin.aset');

        $asset = Asset::query()->sole();
        $this->assertNull($asset->tax_due_date);
        $this->assertNull($asset->next_oil_service_km);
        $this->assertNull($asset->last_oil_service_date);
    }

    public function test_invalid_category_and_future_acquisition_year_are_rejected(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.aset.store'), [
            'name' => 'Aset Tidak Valid',
            'category' => 'kategori-bebas',
            'condition' => AssetCondition::Good->value,
            'acquisition_year' => now()->year + 1,
        ]);

        $response->assertInvalid(['category', 'acquisition_year']);
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_asset_delete_uses_soft_delete(): void
    {
        $admin = User::factory()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.aset.destroy', $asset));

        $response->assertRedirectToRoute('admin.aset');
        $response->assertSessionHas('status', 'Aset berhasil dihapus.');
        $this->assertSoftDeleted($asset);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $asset->id,
            'module' => 'assets',
            'action' => 'deleted',
        ]);
    }

    public function test_admin_can_update_an_asset_and_keep_its_current_code(): void
    {
        $admin = User::factory()->create();
        $asset = Asset::factory()->create([
            'asset_code' => 'AST-EDIT-001',
            'name' => 'Nama Lama',
            'category' => AssetCategory::Vehicle,
            'tax_due_date' => '2027-01-01',
            'next_oil_service_km' => 10000,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.aset.update', $asset), [
            'asset_context_id' => $asset->id,
            'asset_code' => 'AST-EDIT-001',
            'name' => 'Nama Aset Diperbarui',
            'category' => AssetCategory::Vehicle->value,
            'condition' => AssetCondition::NeedsRepair->value,
            'location' => 'Gudang Baru',
            'acquisition_year' => 2025,
            'tax_due_date' => '2028-02-02',
            'next_oil_service_km' => 20000,
        ]);

        $response->assertRedirectToRoute('admin.aset');
        $response->assertSessionHas('status', 'Aset berhasil diperbarui.');
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_code' => 'AST-EDIT-001',
            'name' => 'Nama Aset Diperbarui',
            'condition' => AssetCondition::NeedsRepair->value,
            'location' => 'Gudang Baru',
            'next_oil_service_km' => 20000,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $asset->id,
            'module' => 'assets',
            'action' => 'updated',
        ]);
    }

    public function test_changing_vehicle_to_non_vehicle_clears_vehicle_only_fields(): void
    {
        $admin = User::factory()->create();
        $asset = Asset::factory()->create([
            'category' => AssetCategory::Vehicle,
            'tax_due_date' => '2027-01-01',
            'next_oil_service_km' => 10000,
            'last_oil_service_date' => now()->subMonths(3)->toDateString(),
        ]);

        $this->actingAs($admin)->put(route('admin.aset.update', $asset), [
            'asset_context_id' => $asset->id,
            'asset_code' => $asset->asset_code,
            'name' => $asset->name,
            'category' => AssetCategory::Equipment->value,
            'condition' => AssetCondition::Good->value,
            'tax_due_date' => '2028-02-02',
            'next_oil_service_km' => 20000,
            'last_oil_service_date' => '2026-06-09',
        ])->assertRedirectToRoute('admin.aset');

        $asset->refresh();
        $this->assertSame(AssetCategory::Equipment, $asset->category);
        $this->assertNull($asset->tax_due_date);
        $this->assertNull($asset->next_oil_service_km);
        $this->assertNull($asset->last_oil_service_date);
    }

    public function test_asset_code_must_remain_unique_when_updating(): void
    {
        $admin = User::factory()->create();
        Asset::factory()->create(['asset_code' => 'AST-USED']);
        $asset = Asset::factory()->create(['asset_code' => 'AST-EDIT']);

        $response = $this->actingAs($admin)->put(route('admin.aset.update', $asset), [
            'asset_context_id' => $asset->id,
            'asset_code' => 'AST-USED',
            'name' => $asset->name,
            'category' => $asset->category->value,
            'condition' => $asset->condition->value,
        ]);

        $response->assertInvalid(['asset_code']);
        $this->assertSame('AST-EDIT', $asset->fresh()->asset_code);
    }
}
