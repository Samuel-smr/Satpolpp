<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_displays_employee_details_without_local_crud_forms(): void
    {
        $employee = $this->employee(['name' => 'Pegawai API']);

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('super_admin.petugas'))
            ->assertOk()->assertSeeText($employee->name)->assertSeeText($employee->employee_number)
            ->assertSeeText('e-Profil')->assertDontSee('petugas-form')->assertDontSeeText('Tambah Petugas');

        Http::assertSentCount(1);
    }

    public function test_admin_cannot_access_employee_directory(): void
    {
        Http::preventStrayRequests();

        $this->actingAs(User::factory()->create())->get(route('super_admin.petugas'))->assertForbidden();

        Http::assertNothingSent();
    }
}
