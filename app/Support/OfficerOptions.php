<?php

namespace App\Support;

final class OfficerOptions
{
    /** @return list<string> */
    public static function positions(): array
    {
        return [
            'Polisi Pamong Praja Mahir pada Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum',
            'Operator Layanan Operasional pada Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Polisi Pamong Praja Ahli Madya pada Bidang Penegakan Peraturan Daerah',
            'Polisi Pamong Praja Mahir pada Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Pengadministrasi Perkantoran pada Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum',
            'Operator Layanan Operasional pada Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum',
            'Kepala Bidang Ketentraman dan Ketertiban Umum',
            'Kepala Satuan Polisi Pamong Praja',
            'Kepala Bidang Penegakan Peraturan Daerah',
            'Polisi Pamong Praja Mahir pada Seksi Pembinaan dan Pengawasan, Bidang Penegakan Peraturan Daerah',
            'Kepala Subbagian Umum dan Kepegawaian, Sekretariat Satpol PP',
            'Penelaah Teknis Kebijakan pada Seksi Penyidikan dan Penindakan, Bidang Penegakan Peraturan Daerah',
            'Polisi Pamong Praja Terampil pada Seksi Pembinaan dan Pengawasan, Bidang Penegakan Peraturan Daerah',
            'Pamong Pemerintahan pada Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum',
            'Pranata Trantibum pada Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Polisi Pamong Praja Mahir pada Seksi Penyidikan dan Penindakan, Bidang Penegakan Peraturan Daerah',
            'Polisi Pamong Praja Penyelia pada Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Polisi Pamong Praja Terampil pada Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Arsiparis Terampil pada Subbagian Umum dan Kepegawaian, Sekretariat Satpol PP',
            'Kepala Subbagian Perencanaan dan Keuangan, Sekretariat Satpol PP',
            'Polisi Pamong Praja Ahli Pertama pada Bidang Penegakan Peraturan Daerah',
            'Kepala Seksi Pembinaan dan Pengawasan, Bidang Penegakan Peraturan Daerah',
            'Kepala Seksi Penyidikan dan Penindakan, Bidang Penegakan Peraturan Daerah',
            'Kepala Seksi Perlindungan Masyarakat, Bidang Ketentraman dan Ketertiban Umum',
            'Sekretaris Satuan Polisi Pamong Praja',
            'Kepala Seksi Pengendalian dan Operasional, Bidang Ketentraman dan Ketertiban Umum',
            'Pengadministrasi Perkantoran pada Subbagian Perencanaan dan Keuangan, Sekretariat Satpol PP',
            'Pengolah Data dan Informasi pada Subbagian Perencanaan dan Keuangan, Sekretariat Satpol PP',
        ];
    }

    /** @return list<string> */
    public static function workUnits(): array
    {
        return ['Seksi Perlindungan Masyarakat', 'Seksi Pengendalian dan Operasional', 'Seksi Pembinaan dan Pengawasan', 'Seksi Penyidikan dan Penindakan', 'Subbagian Umum dan Kepegawaian', 'Subbagian Perencanaan dan Keuangan'];
    }

    /** @return list<string> */
    public static function units(): array
    {
        return ['Bidang Ketentraman dan Ketertiban Umum', 'Bidang Penegakan Peraturan Daerah', 'Sekretariat'];
    }
}
