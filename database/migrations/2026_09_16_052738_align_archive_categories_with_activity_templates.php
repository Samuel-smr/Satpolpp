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
        Schema::table('archives', function (Blueprint $table) {
            $table->string('operational_category')->change();
        });

        DB::table('activity_templates')
            ->whereNotNull('operational_category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['name', 'operational_category'])
            ->unique('operational_category')
            ->each(function (object $template): void {
                DB::table('archives')
                    ->where('operational_category', $template->operational_category)
                    ->update(['operational_category' => $template->name]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('activity_templates')
            ->whereNotNull('operational_category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['name', 'operational_category'])
            ->each(function (object $template): void {
                DB::table('archives')
                    ->where('operational_category', $template->name)
                    ->update(['operational_category' => $template->operational_category]);
            });

        Schema::table('archives', function (Blueprint $table) {
            $table->string('operational_category', 50)->change();
        });
    }
};
