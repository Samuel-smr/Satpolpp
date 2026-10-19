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
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('reporter_nik');
        });
    }

    /**
     * Restore the column only; removed NIK values cannot be recovered.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->text('reporter_nik')->nullable();
        });
    }
};
