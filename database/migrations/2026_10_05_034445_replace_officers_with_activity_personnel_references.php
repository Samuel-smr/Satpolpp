<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('activity_officer')->leftJoin('officers', 'officers.id', '=', 'activity_officer.officer_id')->whereNull('officers.employee_number')->exists()) {
            throw new RuntimeException('Ada penugasan tanpa NIP. Perbaiki referensi sebelum melanjutkan migrasi.');
        }

        if (! Schema::hasTable('activity_personnel')) {
            Schema::create('activity_personnel', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->restrictOnDelete();
                $table->string('employee_nip', 50);
                $table->string('assignment_role', 150)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['activity_id', 'employee_nip']);
            });
        }

        DB::table('activity_officer')
            ->join('officers', 'officers.id', '=', 'activity_officer.officer_id')
            ->select(['activity_officer.id', 'activity_officer.activity_id', 'officers.employee_number', 'activity_officer.assignment_role', 'activity_officer.notes', 'activity_officer.created_at', 'activity_officer.updated_at'])
            ->chunkById(200, function ($assignments): void {
                $rows = $assignments->map(fn (object $assignment): array => [
                    'activity_id' => $assignment->activity_id,
                    'employee_nip' => $assignment->employee_number,
                    'assignment_role' => $assignment->assignment_role,
                    'notes' => $assignment->notes,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ])->all();
                DB::table('activity_personnel')->upsert($rows, ['activity_id', 'employee_nip'], ['assignment_role', 'notes', 'created_at', 'updated_at']);
            }, 'activity_officer.id', 'id');

        if (DB::table('activity_personnel')->count() < DB::table('activity_officer')->count()) {
            throw new RuntimeException('Referensi peserta belum seluruhnya dipindahkan. Tabel lama dipertahankan.');
        }

        $legacyCacheKey = 'eprofil:employees:'.hash('sha256', (string) config('services.eprofil.url').'|'.(string) config('services.eprofil.token'));
        Cache::forget($legacyCacheKey);
        Cache::forget($legacyCacheKey.':last-success');

        Schema::drop('activity_officer');
        Schema::drop('officers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Data induk pegawai telah dihapus. Migrasi ini tidak dapat dibalik; gunakan migrasi lanjutan.');
    }
};
