# Penjelasan ERD SIPRAJA

Pembaruan: 28 Oktober 2026. Acuan: kode `503d836` dan seluruh migrasi yang tersedia.

## Ruang lingkup

Diagram `SIPRAJA-ERD.drawio` menunjukkan skema setelah migrasi terakhir,
bukan hanya skema awal September. Tabel framework dipisahkan pada tab tersendiri
agar tidak tercampur dengan domain bisnis. Direktori pegawai e-Profil adalah
sistem eksternal, bukan tabel lokal. Tahun data merupakan cakupan query
berdasarkan tanggal dan nilai sesi; tidak ada tabel atau kolom `data_year`
pada setiap entitas.

## Panduan halaman Draw.io

Pilih tab di bagian bawah editor Draw.io untuk membuka rincian:

| Tab | Isi |
|---|---|
| 01 - Peta domain | Seluruh 13 tabel domain dan alur relasi utama; kolom diringkas. |
| 02 - Laporan dan bukti | Semua kolom users, guest_accesses, reports, report_status_histories, dan attachments; FK pembuat, penanggung jawab, pengubah status, serta pengunggah. |
| 03 - Kegiatan dan personel | Semua kolom activities, activity_templates, activity_personnel, dan activity_findings; relasi laporan, akun, dokumentasi, dan referensi API e-Profil. |
| 04 - Arsip audit notifikasi | Semua kolom archives, archive_files, audit_logs, dan notifications; pemilik, aktor, dan penerima. |
| 05 - Infrastruktur Laravel | sessions, password_reset_tokens, cache, cache_locks, jobs, job_batches, dan failed_jobs beserta status pemakaiannya. |

Garis utuh dengan crow's foot menunjukkan foreign key. Ujung satu/opsional/banyak
menunjukkan kardinalitas skema. Garis putus menunjukkan relasi polimorfik atau
referensi logis, bukan foreign key fisik. `PK` berarti primary key, `FK` foreign
key, `UQ` unik, `IDX` indeks, dan `[NULL]` berarti kolom boleh kosong. Tipe data
mengikuti definisi logis migrasi Laravel, bukan dump dari engine tertentu.
Aturan `SET NULL`, `RESTRICT`, atau `CASCADE` berlaku pada penghapusan fisik;
soft delete tidak menjalankan penghapusan fisik tersebut.

## Entitas dan fungsi

### users

Akun internal Admin/Super Admin dengan status aktif atau diblokir. Menyimpan
nama, username, email opsional, nomor telepon opsional, dan hash password. User
dapat menjadi pembuat laporan, penanggung jawab laporan, pembuat/evaluator
kegiatan, pembuat arsip, pengubah status, pengunggah lampiran, dan aktor audit.

### guest_accesses

Hash token akses browser pelapor, waktu penggunaan, dan kedaluwarsa. Satu token
dapat memiliki banyak laporan. Token mentah berada di cookie HttpOnly, bukan
pada kolom database. Kode laporan tidak menggantikan pemeriksaan token.

### reports

Laporan tunggal bertipe `lapor`, sumber/platform, nama opsional, telepon,
deskripsi, alamat, koordinat opsional, status, waktu selesai, dan feedback.
`reporter_nik` **sudah dihapus** melalui migrasi `2026_10_19_125442`.

- `guest_access_id`: relasi opsional untuk laporan warga.
- `created_by_user_id`: pembuat opsional untuk laporan yang dicatat Admin.
- `assigned_admin_id`: penanggung jawab opsional ke `users`, `nullOnDelete`.
  Validasi aplikasi membatasi pilihan pada Admin aktif; foreign key sendiri
  tidak membatasi role/status. Laporan baru belum ditugaskan.
- Relasi ke riwayat status, lampiran polimorfik, dan maksimal satu Activity.
- Feedback disimpan pada laporan, satu kali setelah selesai.
- Status: `submitted`, `received`, `verifying`, `in_progress`, `completed`,
  `rejected`. Penolakan hanya dari `verifying` pada lapisan aplikasi.
- Soft delete; tahun data mengikuti `created_at`.

### report_status_histories

Menyimpan laporan, status, waktu, catatan, serta user pengubah opsional. Riwayat
awal laporan publik tidak memiliki aktor akun internal.

### attachments

Metadata file untuk Report/Activity dengan pasangan `attachable_type` dan
`attachable_id`; alias morph aktif adalah `report` dan `activity`. Relasi
polimorfik ini bukan foreign key langsung ke dua tabel. `audience`, pemilik
objek, autentikasi, dan tahun data dipakai saat mengotorisasi akses berkas.
`uploaded_by_user_id` opsional. Path berkas merujuk penyimpanan lokal privat.

### activity_templates dan activities

Template menyimpan nama, `operational_category`, deskripsi bawaan, status aktif,
dan urutan. Kategori pada kegiatan memakai kolom `activities.category`;
nama kategori kegiatan internal divalidasi terhadap nama template aktif.
Activity menyimpan kegiatan `internal` atau `external`, template opsional,
pembuat, evaluator opsional, tanggal/waktu, lokasi/koordinat, hasil, serta
evaluasi. `report_id` nullable dan unique; satu laporan maksimal mempunyai satu
kegiatan eksternal, termasuk baris yang telah dihapus lunak. Tahun kegiatan
dan daftar evaluasi mengikuti `activity_date`, bukan `evaluated_at`.

### activity_personnel dan e-Profil

Setiap baris berisi `activity_id` (FK), `employee_nip` (string maksimal 50),
`assignment_role`, `notes`, dan timestamps. Pasangan `(activity_id, employee_nip)`
unik. Kegiatan memiliki nol atau banyak referensi pada tingkat skema; formulir
pembuatan kegiatan mewajibkan minimal satu personel.

NIP adalah referensi ke layanan e-Profil, **bukan FK ke tabel pegawai lokal**.
Nama dan jabatan diperoleh saat request melalui `EprofilService`, direpresentasikan
oleh objek `App\Support\Employee`. Tabel `officers` dan pivot `activity_officer`
sudah dihapus setelah referensi NIP dipindahkan pada migrasi 5 Oktober 2026.

### activity_findings

Nol atau banyak temuan per kegiatan: jenis, jumlah, dan satuan. Form membatasi
50 baris dan jumlah berupa bilangan bulat positif.

### archives dan archive_files

Archive memiliki pembuat, judul, kategori pada `operational_category`, deskripsi opsional, sifat dokumen,
dan soft delete. ArchiveFile menyimpan metadata file per arsip. Pada tingkat
skema arsip dapat mempunyai nol atau banyak file; formulir membuat minimal satu
dan membatasi total lima file (20 MB per file). Tahun arsip mengikuti `created_at`.

### notifications dan audit_logs

Notifications menggunakan skema polimorfik notifikasi database Laravel per akun,
termasuk payload dan waktu dibaca. AuditLog mencatat aktor opsional, subjek,
modul/aksi, deskripsi, nilai lama/baru, IP, user agent, dan waktu. Perubahan
penanggung jawab tercatat sebagai aksi `assigned` pada modul `reports`.
Keduanya dibatasi `created_at` dalam tampilan tahun terpilih.

Pencatatan audit dipanggil secara eksplisit oleh controller untuk kegiatan,
evaluasi, arsip, penugasan/status/penolakan/penyelesaian/penghapusan laporan,
penghapusan dokumentasi, dan profil. Pembuatan laporan, login/logout, serta CRUD
akun melalui Manajemen User belum mempunyai pencatatan audit. Keberadaan tabel
audit tidak berarti semua perubahan database otomatis terpantau.

## Tabel pendukung framework

`sessions.user_id` memiliki indeks, tetapi tidak mempunyai FK pada migrasi.
Sesi dipakai antara lain untuk login, tahun data, verifikasi OTP, dan riwayat AI.
Cache dan lock mengikuti driver konfigurasi; kode OTP menggunakan cache.

`password_reset_tokens` masih dibuat oleh migrasi dan dirujuk `config/auth.php`,
tetapi SIPRAJA belum menyediakan alur lupa password melalui email. Perubahan
password pada Profil dan Manajemen User langsung memperbarui hash di `users`.
Tabel ini tidak menyimpan OTP WhatsApp.

Tabel jobs, job_batches, dan failed_jobs adalah infrastruktur antrean; tidak ada
FK antar ketiganya dalam migrasi. Keberadaan tabel tidak menyatakan ada job aktif.
Tabel migrations merupakan catatan migrasi framework dan tidak digambar sebagai
entitas bisnis.

## Kardinalitas utama

| Relasi | Kardinalitas / batas |
|---|---|
| GuestAccess → Report | 1 ke banyak; GuestAccess opsional pada Report |
| User → Report (pembuat) | 1 ke banyak; pembuat opsional |
| User → Report (penanggung jawab) | 1 ke banyak; penanggung jawab opsional |
| Report → ReportStatusHistory | 1 ke banyak |
| Report → Activity | 1 ke 0 atau 1 (`report_id` unique) |
| Report/Activity → Attachment | 1 ke banyak, polimorfik |
| ActivityTemplate → Activity | 1 ke banyak; template opsional |
| User → Activity | 1 ke banyak sebagai pembuat/evaluator |
| Activity → ActivityPersonnel | 1 ke banyak; unik kegiatan/NIP |
| ActivityPersonnel → e-Profil | Referensi NIP ke layanan luar; tanpa FK lokal |
| Activity → ActivityFinding | 1 ke banyak |
| User → Archive → ArchiveFile | 1 ke banyak pada masing-masing relasi |

## Catatan migrasi

Tidak ada tabel aktif `operational_zones`, `organizational_units`, `assets`,
`officers`, atau `activity_officer`, maupun kolom `reports.reporter_nik` setelah
seluruh migrasi diterapkan. Indeks tanggal ditambahkan pada laporan, kegiatan,
arsip, dan audit untuk pemilihan tahun. Jangan menganggap tabel lama masih aktif
hanya karena file migrasi pembuatannya tetap tersimpan di repositori.
