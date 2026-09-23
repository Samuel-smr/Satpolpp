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
        Schema::dropIfExists('assets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('asset_code', 50)->nullable()->unique();
            $table->string('name');
            $table->string('category', 30);
            $table->string('condition', 30);
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('acquisition_year')->nullable();
            $table->date('tax_due_date')->nullable();
            $table->unsignedInteger('next_oil_service_km')->nullable();
            $table->date('last_oil_service_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'condition']);
        });
    }
};
