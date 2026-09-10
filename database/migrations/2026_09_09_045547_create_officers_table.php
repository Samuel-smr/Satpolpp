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
        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('organizational_unit_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->foreignId('operational_zone_id')->nullable()->constrained('operational_zones')->restrictOnDelete();
            $table->string('name');
            $table->string('employee_number', 50)->unique();
            $table->string('rank', 100)->nullable();
            $table->string('position', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 25)->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('status', 30);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organizational_unit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
