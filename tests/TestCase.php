<?php

namespace Tests;

use App\Services\WhatsAppService;
use App\Support\Employee;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /** @var list<array{nama: string, nip: string, jabatan_nama: string}> */
    private array $apiEmployees = [];

    private bool $employeeApiFaked = false;

    /** @param array<string, mixed> $attributes */
    protected function employee(array $attributes = [], bool $available = true): Employee
    {
        if (! $this->employeeApiFaked) {
            config()->set('services.eprofil', ['url' => 'https://eprofil.example.test/pegawai', 'token' => 'test-token']);
            config()->set('services.whatsapp.bot_url', 'http://localhost:3456');
            Http::preventStrayRequests();
            Http::fake([
                'https://eprofil.example.test/pegawai' => fn () => Http::response(['success' => true, 'data' => $this->apiEmployees]),
                'http://localhost:3456/send' => Http::response(['status' => true]),
            ]);
            $this->employeeApiFaked = true;
        }

        $employee = new Employee(
            employee_number: $attributes['employee_number'] ?? fake()->unique()->numerify('##################'),
            name: $attributes['name'] ?? fake()->name(),
            position: $attributes['position'] ?? 'Petugas Lapangan',
        );

        if ($available) {
            $this->apiEmployees[] = ['nama' => $employee->name, 'nip' => $employee->employee_number, 'jabatan_nama' => $employee->position];
        }

        return $employee;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, Employee>
     */
    protected function employees(int $count, array $attributes = []): Collection
    {
        return collect(range(1, $count))->map(fn (): Employee => $this->employee($attributes));
    }

    /** Menandai nomor telepon sebagai sudah diverifikasi OTP di cache. */
    protected function verifyPhoneOtp(string $phone): void
    {
        Cache::put(
            'sipraja_otp:verified:'.hash('sha256', app(WhatsAppService::class)->normalizePhone($phone)),
            true,
            now()->addMinutes(15),
        );
    }
}
