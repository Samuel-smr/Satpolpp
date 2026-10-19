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
        Schema::table('reports', function (Blueprint $table): void {
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('created_at');
        });

        Schema::table('activities', fn (Blueprint $table) => $table->index('activity_date'));
        Schema::table('archives', fn (Blueprint $table) => $table->index('created_at'));
        Schema::table('audit_logs', fn (Blueprint $table) => $table->index('created_at'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', fn (Blueprint $table) => $table->dropIndex(['created_at']));
        Schema::table('archives', fn (Blueprint $table) => $table->dropIndex(['created_at']));
        Schema::table('activities', fn (Blueprint $table) => $table->dropIndex(['activity_date']));
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
            $table->dropConstrainedForeignId('assigned_admin_id');
        });
    }
};
