<?php

namespace App\Services;

use App\Models\ActivityPersonnel;
use App\Support\Employee;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class EprofilService
{
    public function enabled(): bool
    {
        return trim((string) config('services.eprofil.url')) !== '';
    }

    public function warning(): ?string
    {
        return request()->attributes->get('eprofil.warning');
    }

    /** @return Collection<int, Employee> */
    public function officers(): Collection
    {
        if (request()->attributes->has('eprofil.employees')) {
            return request()->attributes->get('eprofil.employees');
        }

        try {
            $employees = collect($this->fetchEmployees())->sortBy('name')->values();
        } catch (Throwable $exception) {
            Log::warning('Daftar pegawai e-Profil tidak dapat dimuat.', ['exception_type' => $exception::class]);
            $employees = collect();
            request()->attributes->set('eprofil.warning', 'Data pegawai e-Profil belum dapat dimuat. Silakan coba lagi nanti.');
        }
        request()->attributes->set('eprofil.employees', $employees);

        return $employees;
    }

    /**
     * @param  Collection<int, ActivityPersonnel>  $personnel
     * @return Collection<int, Employee>
     */
    public function resolvePersonnel(Collection $personnel): Collection
    {
        if ($personnel->isEmpty()) {
            return collect();
        }

        $employees = $this->officers()->keyBy('employee_number');

        return $personnel->map(fn (ActivityPersonnel $participant): Employee => $employees->get($participant->employee_nip) ?? new Employee($participant->employee_nip, 'Pegawai tidak tersedia')
        )->values();
    }

    /** @return list<Employee> */
    private function fetchEmployees(): array
    {
        $token = trim((string) config('services.eprofil.token'));

        if (! $this->enabled() || $token === '') {
            throw new RuntimeException('API atau token e-Profil belum dikonfigurasi.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->connectTimeout(3)
            ->timeout(10)
            ->retry([250], 0, fn (?Throwable $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException
                    && ($exception->response->status() === 429 || $exception->response->serverError())))
            ->get((string) config('services.eprofil.url'))
            ->throw();

        $payload = $response->json();

        if (! is_array($payload) || ($payload['success'] ?? null) !== true || ! is_array($payload['data'] ?? null) || ! array_is_list($payload['data'])) {
            throw new RuntimeException('Respons daftar pegawai e-Profil tidak valid.');
        }

        $validated = Validator::make($payload, [
            'data' => ['present', 'array'],
            'data.*.nama' => ['required', 'string', 'max:255'],
            'data.*.nip' => ['required', 'string', 'max:50', 'regex:/\A[0-9]+\z/', 'distinct:strict'],
            'data.*.jabatan_nama' => ['required', 'string', 'max:255'],
        ])->validate();

        return array_map(fn (array $employee): Employee => new Employee(
            employee_number: trim($employee['nip']),
            name: trim($employee['nama']),
            position: trim($employee['jabatan_nama']),
        ), $validated['data']);
    }
}
