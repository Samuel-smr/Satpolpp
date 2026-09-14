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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->nullable()->unique()->constrained('reports')->restrictOnDelete();
            $table->foreignId('activity_template_id')->nullable()->constrained('activity_templates')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('evaluated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('type', 30);
            $table->string('operational_category', 50)->nullable();
            $table->date('activity_date');
            $table->time('start_time')->nullable();
            $table->text('description')->nullable();
            $table->text('handling_result')->nullable();
            $table->text('location_address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('evaluation_notes')->nullable();
            $table->text('evaluation_obstacles')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
