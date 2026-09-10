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
            self::PublicOrderPatrol => 'Patroli Ketertiban Umum',
            self::OfficialSecurityEscort => 'Pengamanan dan Pengawalan Pejabat',
            self::EventSecurity => 'Pengamanan Acara atau Kegiatan',
            self::PublicComplaintHandling => 'Penanganan Pengaduan Masyarakat',
            self::RegionalRegulationCompliance => 'Kepatuhan Perda dan Perkada',
            self::AttributeSupervision => 'Pembinaan dan Pengawasan Atribut',
            self::JudicialEnforcement => 'Penegakan Yustisial',
            self::NonJudicialEnforcement => 'Penegakan Non-Yustisial',
        };
    }
}
