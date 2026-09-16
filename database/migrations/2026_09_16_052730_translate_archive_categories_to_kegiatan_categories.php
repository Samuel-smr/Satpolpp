<?php

use App\ArchiveCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_VALUES = [
        'patroli_ketertiban_umum' => 'Patroli Ketertiban Umum, Ketenteraman Masyarakat dan Pelindungan Masyarakat',
        'pengamanan_pengawalan_pejabat' => 'Pengamanan dan Pengawalan Pejabat VIP',
        'pengamanan_acara_kegiatan' => 'Pengamanan Aset, Acara atau Event',
        'penanganan_pengaduan_masyarakat' => 'Penanganan Pengaduan Pelanggaran Ketertiban Umum dan Ketenteraman Masyarakat',
        'kepatuhan_perda_dan_perkada' => 'Penegakan Perda dan Perkada',
        'pembinaan_pengawasan_atribut' => 'Pembinaan dan Pengawasan Atribut',
        'penegakan_yustisial' => ArchiveCategory::JURIDICAL,
        'penegakan_non_yustisial' => ArchiveCategory::NON_JURIDICAL,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::LEGACY_VALUES as $oldValue => $newValue) {
            DB::table('archives')->where('operational_category', $oldValue)->update(['operational_category' => $newValue]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::LEGACY_VALUES as $oldValue => $newValue) {
            DB::table('archives')->where('operational_category', $newValue)->update(['operational_category' => $oldValue]);
        }
    }
};
