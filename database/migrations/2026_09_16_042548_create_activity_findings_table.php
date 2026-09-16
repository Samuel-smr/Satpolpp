<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedInteger('quantity');
            $table->string('unit', 20);
            $table->timestamps();
        });

        DB::table('activities')
            ->where(function ($query): void {
                $query->whereNotNull('finding_type')
                    ->orWhereNotNull('finding_quantity')
                    ->orWhereNotNull('finding_unit');
            })
            ->orderBy('id')
            ->each(function (object $activity): void {
                if ($activity->finding_type === null || $activity->finding_quantity === null || $activity->finding_unit === null) {
                    return;
                }

                DB::table('activity_findings')->insert([
                    'activity_id' => $activity->id,
                    'type' => $activity->finding_type,
                    'quantity' => $activity->finding_quantity,
                    'unit' => $activity->finding_unit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['finding_type', 'finding_quantity', 'finding_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('finding_type', 20)->nullable()->after('handling_result');
            $table->unsignedInteger('finding_quantity')->nullable()->after('finding_type');
            $table->string('finding_unit', 20)->nullable()->after('finding_quantity');
        });

        DB::table('activity_findings')
            ->orderBy('id')
            ->get()
            ->groupBy('activity_id')
            ->each(function ($findings, int $activityId): void {
                $finding = $findings->first();

                DB::table('activities')->where('id', $activityId)->update([
                    'finding_type' => $finding->type,
                    'finding_quantity' => $finding->quantity,
                    'finding_unit' => $finding->unit,
                ]);
            });

        Schema::dropIfExists('activity_findings');
    }
};
