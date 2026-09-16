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
        Schema::create('archive_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archive_id')->constrained()->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->string('file_extension', 20);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
        });

        DB::table('archives')->orderBy('id')->each(function (object $archive): void {
            DB::table('archive_files')->insert([
                'archive_id' => $archive->id,
                'file_path' => $archive->file_path,
                'original_name' => $archive->original_name,
                'mime_type' => $archive->mime_type,
                'file_extension' => $archive->file_extension,
                'file_size' => $archive->file_size,
                'created_at' => $archive->created_at,
                'updated_at' => $archive->updated_at,
            ]);
        });

        Schema::table('archives', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'original_name', 'mime_type', 'file_extension', 'file_size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
        });

        DB::table('archive_files')->orderBy('id')->get()->groupBy('archive_id')->each(function ($files, int $archiveId): void {
            $file = $files->first();
            DB::table('archives')->where('id', $archiveId)->update([
                'file_path' => $file->file_path,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'file_extension' => $file->file_extension,
                'file_size' => $file->file_size,
            ]);
        });

        Schema::dropIfExists('archive_files');
    }
};
