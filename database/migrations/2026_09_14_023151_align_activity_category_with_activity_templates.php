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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('category')->nullable()->change();
        });

        DB::table('activities')->where('type', 'external')->update(['category' => null]);

        DB::table('activity_templates')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->each(fn (object $template): int => DB::table('activities')
                ->where('type', 'internal')
                ->where('activity_template_id', $template->id)
                ->update(['category' => $template->name]));

        DB::table('activities')
            ->where('type', 'internal')
            ->whereNull('activity_template_id')
            ->update(['category' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('activity_templates')
            ->select(['id', 'operational_category'])
            ->orderBy('id')
            ->each(fn (object $template): int => DB::table('activities')
                ->where('type', 'internal')
                ->where('activity_template_id', $template->id)
                ->update(['category' => $template->operational_category]));

        DB::table('activities')->where('type', 'external')->update(['category' => null]);
        DB::table('activities')->whereNull('activity_template_id')->update(['category' => null]);

        Schema::table('activities', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->change();
        });
    }
};
