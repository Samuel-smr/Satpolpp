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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('guest_access_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->string('category', 50)->nullable();
            $table->string('source', 30);
            $table->string('source_platform', 30)->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 25);
            $table->text('reporter_nik')->nullable();
            $table->text('description');
            $table->text('location_address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30)->default('submitted');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('feedback_rating')->nullable();
            $table->text('feedback_comment')->nullable();
            $table->timestamp('feedback_submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['guest_access_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
