<?php

namespace App;

enum OperationalCategory: string
{
    case PublicOrderPatrol = 'patroli_ketertiban_umum';
    case OfficialSecurityEscort = 'pengamanan_pengawalan_pejabat';
    case EventSecurity = 'pengamanan_acara_kegiatan';
    case PublicComplaintHandling = 'penanganan_pengaduan_masyarakat';
    case RegionalRegulationCompliance = 'kepatuhan_perda_dan_perkada';
    case AttributeSupervision = 'pembinaan_pengawasan_atribut';
    case JudicialEnforcement = 'penegakan_yustisial';
    case NonJudicialEnforcement = 'penegakan_non_yustisial';

    public function label(): string
    {
        return match ($this) {
            self::PublicOrderPatrol => 'Patroli ketertiban umum, ketentraman, dan perlindungan masyarakat',
            self::OfficialSecurityEscort => 'Pengamanan dan pengawalan pejabat daerah',
            self::EventSecurity => 'Pengamanan acara atau event',
            self::PublicComplaintHandling => 'Penanganan pengaduan pelanggaran ketertiban umum/masyarakat',
            self::RegionalRegulationCompliance => 'Pembinaan dan pengawasan kepatuhan terhadap Perda dan Perkada',
            self::AttributeSupervision => 'Pembinaan dan pengawasan atribut',
            self::JudicialEnforcement => 'Penegakan peraturan daerah dan peraturan kepala daerah yustisi',
            self::NonJudicialEnforcement => 'Penegakan peraturan daerah dan peraturan kepala daerah non yustisi',
        };
    }
}
