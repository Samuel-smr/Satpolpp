<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->categoryMap() as $oldValue => $newValue) {
            DB::table('activity_templates')->where('operational_category', $oldValue)->update(['operational_category' => $newValue]);
            DB::table('activities')->where('operational_category', $oldValue)->update(['operational_category' => $newValue]);
            DB::table('archives')->where('operational_category', $oldValue)->update(['operational_category' => $newValue]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->categoryMap() as $oldValue => $newValue) {
            DB::table('activity_templates')->where('operational_category', $newValue)->update(['operational_category' => $oldValue]);
            DB::table('activities')->where('operational_category', $newValue)->update(['operational_category' => $oldValue]);
            DB::table('archives')->where('operational_category', $newValue)->update(['operational_category' => $oldValue]);
        }
    }

    /** @return array<string, string> */
    private function categoryMap(): array
    {
        return [
            'public_order_patrol' => 'patroli_ketertiban_umum',
            'official_security_escort' => 'pengamanan_pengawalan_pejabat',
            'event_security' => 'pengamanan_acara_kegiatan',
            'public_complaint_handling' => 'penanganan_pengaduan_masyarakat',
            'regional_regulation_compliance' => 'kepatuhan_perda_dan_perkada',
            'attribute_supervision' => 'pembinaan_pengawasan_atribut',
            'judicial_enforcement' => 'penegakan_yustisial',
            'non_judicial_enforcement' => 'penegakan_non_yustisial',
        ];
    }
};
