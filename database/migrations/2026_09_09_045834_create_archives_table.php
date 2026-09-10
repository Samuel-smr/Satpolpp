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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('operational_category', 50);
            $table->text('description')->nullable();
            $table->string('confidentiality', 30);
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->string('file_extension', 20);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['operational_category', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
