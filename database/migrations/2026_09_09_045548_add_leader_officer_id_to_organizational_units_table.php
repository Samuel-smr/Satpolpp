<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizational_units', function (Blueprint $table) {
            $table->foreignId('leader_officer_id')->nullable()->constrained('officers')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizational_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_officer_id');
        });
    }
};
