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
        Schema::table('officers', function (Blueprint $table) {
            $table->string('work_unit_name', 150)->nullable()->after('position');
            $table->string('unit_name', 150)->nullable()->after('work_unit_name');
            $table->string('position', 255)->nullable()->change();
            $table->dropColumn(['rank', 'email', 'phone', 'address', 'joined_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn(['work_unit_name', 'unit_name']);
            $table->string('position', 150)->nullable()->change();
            $table->string('rank', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 25)->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
        });
    }
};
