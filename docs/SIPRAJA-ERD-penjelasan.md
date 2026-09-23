# Penjelasan ERD SIPRAJA

Pembaruan: 21 September 2026

## Ruang lingkup

ERD menggambarkan tabel domain aktif setelah penyederhanaan laporan dan penghapusan struktur organisasi. Tabel framework Laravel seperti cache, jobs, sessions, dan migrations tidak ditampilkan agar diagram tetap fokus.

## Entitas dan fungsi

### users

Menyimpan akun internal dengan role Admin atau Super Admin. User dapat menjadi pembuat laporan internal, pembuat/evaluator kegiatan, pembuat arsip, pengubah status, dan aktor audit.

### guest_accesses

Menyimpan hash token browser pelapor. Satu guest access dapat memiliki banyak laporan. Token memungkinkan pelapor mengakses laporan sendiri tanpa akun.

### reports

Menyimpan laporan masyarakat atau laporan yang dicatat Admin. Nilai `type` aktif hanya `lapor`. `reporter_nik` nullable, tidak ditampilkan pada form publik, dienkripsi bila data lama masih ada, dan disembunyikan dari serialisasi model.

Relasi penting:

- opsional ke `guest_accesses` untuk laporan warga;
- opsional ke `users` untuk laporan yang dibuat Admin;
- satu-ke-banyak ke riwayat status;
- lampiran polimorfik;
- maksimal satu kegiatan eksternal;
- feedback tersimpan pada baris laporan setelah selesai.

### report_status_histories

Menyimpan setiap status, waktu, catatan, dan user pengubah. Baris awal laporan warga tidak harus memiliki aktor user.

### attachments

Menyimpan metadata file untuk Report atau Activity menggunakan pasangan `attachable_type` dan `attachable_id`. `audience` menentukan cakupan akses file.

### activity_templates

Menyediakan nama, kategori, deskripsi bawaan, status aktif, dan urutan template kegiatan.

### activities

Menyimpan kegiatan internal serta kegiatan eksternal hasil penyelesaian laporan. `report_id` nullable dan unique sehingga satu laporan maksimal mempunyai satu kegiatan. Kegiatan memiliki pembuat, template opsional, evaluator opsional, lokasi, hasil, dan soft delete.

### officers dan activity_officer

`officers` menyimpan petugas tanpa foreign key organisasi/wilayah. Relasi banyak-ke-banyak dengan kegiatan disimpan pada `activity_officer`, termasuk peran penugasan dan catatan.

### activity_findings

Menyimpan nol atau banyak temuan pada satu kegiatan, masing-masing dengan jenis, jumlah, dan satuan.

### archives dan archive_files

Satu arsip dimiliki pembuat dan mempunyai satu atau beberapa file. File dipisahkan agar satu folder arsip dapat menyimpan beberapa dokumen.

### notifications

Menggunakan skema notifikasi database Laravel untuk notifikasi per user.

### audit_logs

Mencatat aktor, subjek opsional, modul, aksi, deskripsi, perubahan terstruktur, IP, user agent, dan waktu.

## Kardinalitas utama

| Relasi | Kardinalitas |
|---|---|
| GuestAccess → Report | 1 ke banyak |
| User → Report | 1 ke banyak, opsional pada Report |
| Report → ReportStatusHistory | 1 ke banyak |
| Report → Activity | 1 ke 0 atau 1 |
| Report/Activity → Attachment | 1 ke banyak, polimorfik |
| ActivityTemplate → Activity | 1 ke banyak, opsional pada Activity |
| User → Activity | 1 ke banyak sebagai pembuat/evaluator |
| Activity ↔ Officer | banyak ke banyak melalui activity_officer |
| Activity → ActivityFinding | 1 ke banyak |
| Archive → ArchiveFile | 1 ke banyak |

## Perubahan yang wajib tercermin pada diagram

- Tidak ada tabel `operational_zones`.
- Tidak ada tabel `organizational_units`.
- Officer tidak mempunyai `organizational_unit_id` atau `operational_zone_id`.
- `reports.reporter_nik` tetap ada tetapi nullable dan bukan input publik.
- `reports.type` hanya menggunakan nilai `lapor` pada data aktif.
- Tidak ada tabel `assets`; fitur Aset dan peringatan kendaraan sudah dihapus.
