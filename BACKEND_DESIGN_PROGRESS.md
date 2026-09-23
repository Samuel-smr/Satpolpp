# SIPRAJA — Backend Design & Implementation Status

Dokumen ini adalah snapshot implementasi aktual per 21 September 2026. Route, controller, model, migration, dan pengujian aplikasi menjadi sumber kebenaran utama.

## Status ringkas

Backend utama sudah terhubung ke database untuk autentikasi, laporan, status, penyelesaian, kegiatan, petugas, evaluasi, arsip, feedback, notifikasi, monitoring, dan profil. Laporan publik telah disederhanakan menjadi satu jenis; struktur unit organisasi/wilayah dan fitur Aset telah dihapus.

## Keputusan domain terbaru

1. Semua laporan memakai `reports.type = lapor`; tidak ada lagi pilihan Darurat maupun Informasi & Pengaduan.
2. Form publik tidak meminta NIK. `reporter_nik` tetap nullable di database untuk kompatibilitas sementara, dienkripsi bila berisi data lama, dan disembunyikan dari serialisasi.
3. Data laporan hanya dapat dilihat oleh Admin dalam alur penanganan dan Super Admin pada akses detail baca-saja. Pelapor hanya dapat melihat laporan yang terikat pada token tamunya.
4. Peta persebaran tidak mempunyai menu/route terpisah; komponen peta berada pada dashboard Super Admin.
5. Manajemen Organisasi dihapus. Tabel `operational_zones` dan `organizational_units` serta foreign key petugas ke keduanya dihapus melalui migration `2026_09_21_051148_simplify_reports_and_remove_organization_structure.php`.
6. Petugas berdiri sendiri dengan identitas NIP, nama, jabatan, satuan kerja, unit kerja, dan status.
7. Asisten AI pada halaman Lapor menggunakan Google Gemini melalui backend Laravel agar API key tidak terekspos ke browser. Model default adalah `gemini-3.1-flash-lite`.
8. Fitur Aset, peringatan pajak/servis, route, model, dan tabel `assets` dihapus melalui migration `2026_09_21_115326_drop_assets_table.php`.

## Modul yang tersedia

### Publik/pelapor

- Landing page, form Lapor, status laporan, dan feedback.
- Identitas: nomor telepon wajib, nama opsional, tanpa input NIK.
- Isi laporan: deskripsi, alamat, koordinat opsional sesuai form, dan lampiran gambar.
- Guest access menggunakan token hash; kode laporan menjadi identitas yang ditampilkan.
- Asisten AI memakai riwayat maksimal delapan pesan dari sesi server (kedaluwarsa konteks setelah 30 menit tidak aktif), session lock, limit per IP/global, timeout/retry terbatas, redaksi pola data sensitif, allowlist endpoint HTTPS Google tanpa redirect, serta validasi keluaran dan respons kegagalan aman. Model tidak memiliki akses alat, berkas server, database, atau CRUD.

### Admin

- Dashboard dan notifikasi laporan baru.
- Daftar, pencarian, filter, detail, pembuatan, soft delete, dan perubahan status laporan.
- Penyelesaian laporan hanya dari `in_progress`, menghasilkan kegiatan eksternal terkait dan status `completed`.
- Kegiatan internal: tambah, detail, ubah, hapus, lampiran, temuan, petugas, filter, serta ekspor dari antarmuka.
- Evaluasi kegiatan.
- Arsip multi-file.
- Daftar feedback masyarakat.
- Profil dan perubahan kata sandi.

### Super Admin

- Dashboard statistik dan peta persebaran terpadu.
- Detail laporan dan lampiran dalam mode baca-saja.
- Kegiatan internal dan lampirannya.
- CRUD akun Admin beserta blokir/aktifkan.
- CRUD petugas dan perubahan status.
- Monitoring audit log.
- Profil dan perubahan kata sandi.
- Tidak memiliki menu Peta terpisah atau Manajemen Organisasi.

## Skema domain aktif

Tabel aplikasi:

1. `users`
2. `guest_accesses`
3. `reports`
4. `report_status_histories`
5. `attachments`
6. `activity_templates`
7. `activities`
8. `officers`
9. `activity_officer`
10. `activity_findings`
11. `archives`
12. `archive_files`
13. `notifications`
14. `audit_logs`

Tabel infrastruktur Laravel seperti `sessions`, `cache`, `jobs`, dan `migrations` tetap tersedia tetapi bukan bagian dari domain SIPRAJA.

## Relasi utama

- `guest_accesses` 1—N `reports`
- `users` 1—N `reports` melalui `created_by_user_id` untuk laporan yang dicatat Admin
- `reports` 1—N `report_status_histories`
- `reports` 1—0..1 `activities` untuk kegiatan eksternal hasil penyelesaian
- `reports` dan `activities` memiliki `attachments` secara polimorfik
- `activity_templates` 1—N `activities`
- `activities` N—M `officers` melalui `activity_officer`
- `activities` 1—N `activity_findings`
- `archives` 1—N `archive_files`
- `users` menjadi pembuat/evaluator/aktor pada data operasional dan audit yang relevan

## Aturan keamanan

- Autentikasi dan otorisasi dipisahkan berdasarkan role `admin` dan `super_admin`.
- Akun diblokir tidak dapat masuk.
- Endpoint AI dibatasi rate limit dan API key hanya dibaca dari konfigurasi server.
- Input divalidasi melalui Form Request.
- Lampiran dikirim melalui endpoint terotorisasi, bukan melalui path publik langsung.
- Audit log tidak boleh memuat kata sandi, token, API key, NIK lengkap, atau isi file rahasia.
- Perubahan penting memakai transaksi dan penguncian saat risiko permintaan ganda tersedia.

## Status verifikasi perubahan terbaru

- Pengujian fitur laporan publik, Admin, dashboard, peta, skema, seeder, dan Asisten AI tersedia di `tests/Feature`.
- Pengujian khusus Asisten AI mencakup permintaan sukses, validasi, kegagalan aman, dan retry gangguan sementara.
- PHP diformat dengan Laravel Pint.

## Catatan deployment

- Isi `AI_API_KEY` melalui secret/environment hosting, bukan di repository.
- Gunakan `AI_MODEL=gemini-3.1-flash-lite` untuk percakapan ringan dan latensi rendah.
- Jalankan migration terbaru sebelum membuka aplikasi.
- Setelah mengganti environment production, bersihkan dan bangun ulang cache konfigurasi.
- Pastikan scheduler/queue, storage link, izin storage, serta build Vite disiapkan sesuai lingkungan hosting.
