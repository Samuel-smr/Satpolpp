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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('finding_type', 20)->nullable()->after('handling_result');
            $table->unsignedInteger('finding_quantity')->nullable()->after('finding_type');
            $table->string('finding_unit', 20)->nullable()->after('finding_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['finding_type', 'finding_quantity', 'finding_unit']);
        });
    }
};
