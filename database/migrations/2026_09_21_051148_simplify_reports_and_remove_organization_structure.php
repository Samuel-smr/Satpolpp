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
        DB::table('reports')->whereIn('type', ['emergency', 'information'])->update(['type' => 'lapor']);

        $hasOrganizationalUnitForeignKey = Schema::hasForeignKey('officers', ['organizational_unit_id']);
        $hasOperationalZoneForeignKey = Schema::hasForeignKey('officers', ['operational_zone_id']);
        $hasOrganizationStatusIndex = Schema::hasIndex('officers', 'officers_organizational_unit_id_status_index');

        Schema::table('officers', function (Blueprint $table) use ($hasOrganizationalUnitForeignKey, $hasOperationalZoneForeignKey, $hasOrganizationStatusIndex) {
            if ($hasOrganizationalUnitForeignKey) {
                $table->dropForeign(['organizational_unit_id']);
            }

            if ($hasOperationalZoneForeignKey) {
                $table->dropForeign(['operational_zone_id']);
            }

            if ($hasOrganizationStatusIndex) {
                $table->dropIndex(['organizational_unit_id', 'status']);
            }

            $table->dropColumn(['organizational_unit_id', 'operational_zone_id']);
        });

        Schema::drop('organizational_units');
        Schema::drop('operational_zones');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('operational_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 30)->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->foreignId('operational_zone_id')->nullable()->constrained('operational_zones')->restrictOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique();
            $table->string('type', 30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->foreignId('organizational_unit_id')->nullable()->after('user_id')->constrained('organizational_units')->restrictOnDelete();
            $table->foreignId('operational_zone_id')->nullable()->after('organizational_unit_id')->constrained('operational_zones')->restrictOnDelete();
            $table->index(['organizational_unit_id', 'status']);
        });

        Schema::table('organizational_units', function (Blueprint $table) {
            $table->foreignId('leader_officer_id')->nullable()->constrained('officers')->restrictOnDelete();
        });

        DB::table('reports')->where('type', 'lapor')->update(['type' => 'information']);
    }
};
