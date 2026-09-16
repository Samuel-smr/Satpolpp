<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private const TRANSLATED_CATEGORIES = [
        'patroli_ketertiban_umum' => 'Patroli Ketertiban Umum, Ketenteraman Masyarakat dan Pelindungan Masyarakat',
        'pengamanan_pengawalan_pejabat' => 'Pengamanan dan Pengawalan Pejabat VIP',
        'pengamanan_acara_kegiatan' => 'Pengamanan Aset, Acara atau Event',
        'penanganan_pengaduan_masyarakat' => 'Penanganan Pengaduan Pelanggaran Ketertiban Umum dan Ketenteraman Masyarakat',
        'kepatuhan_perda_dan_perkada' => 'Penegakan Perda dan Perkada',
        'pembinaan_pengawasan_atribut' => 'Pembinaan dan Pengawasan Atribut',
        'penegakan_yustisial' => 'Penegakan peraturan daerah dan peraturan kepala daerah yustisi',
        'penegakan_non_yustisial' => 'Penegakan peraturan daerah dan peraturan kepala daerah non yustisi',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('activity_templates')
            ->whereNotNull('operational_category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['name', 'operational_category'])
            ->unique('operational_category')
            ->each(function (object $template): void {
                DB::table('archives')
                    ->whereIn('operational_category', array_filter([
                        $template->operational_category,
                        self::TRANSLATED_CATEGORIES[$template->operational_category] ?? null,
                    ]))
                    ->update(['operational_category' => $template->name]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
