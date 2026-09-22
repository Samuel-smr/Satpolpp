# SIPRAJA Satpol PP Kulon Progo

SIPRAJA adalah aplikasi Laravel untuk menerima laporan masyarakat, mendukung penanganan oleh Admin, dan menyediakan pengawasan operasional bagi Super Admin.

## Kondisi sistem saat ini

- Laporan masyarakat hanya memiliki satu jenis: `lapor`.
- Pelapor tidak perlu login dan tidak diminta memasukkan NIK. Kolom `reports.reporter_nik` masih dipertahankan sebagai nullable untuk kompatibilitas data lama.
- Pelapor memperoleh kode laporan dan token tamu untuk melihat status serta mengirim feedback setelah laporan selesai.
- Halaman Lapor menyediakan Asisten AI berbasis Google Gemini untuk panduan dan penyusunan deskripsi. Percakapan dibatasi untuk konteks pelaporan dan tidak boleh meminta data sensitif.
- Admin menangani laporan, status, penyelesaian, kegiatan, evaluasi, arsip, dan feedback.
- Super Admin mengelola akun Admin, petugas, kegiatan, monitoring, serta melihat ringkasan dan peta persebaran langsung pada dashboard.
- Menu Peta terpisah dan Manajemen Organisasi sudah dihapus.
- Fitur Aset beserta tabel `assets` sudah dihapus.
- Tabel `operational_zones` dan `organizational_units`, berikut relasinya pada petugas, sudah dihapus.

## Teknologi

- PHP 8.5
- Laravel 13
- MySQL
- Blade, Tailwind CSS, dan Vite
- PHPUnit
- Google Gemini API (`gemini-3.1-flash-lite` secara default)

## Menjalankan aplikasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
composer run dev
```

Konfigurasikan database serta integrasi AI pada `.env`:

```dotenv
AI_API_KEY=
AI_ENABLED=true
AI_HOURLY_LIMIT=60
AI_DAILY_LIMIT=1000
AI_MODEL=gemini-3.1-flash-lite
```

Jangan menyimpan API key asli di Git. Setelah mengubah `.env` pada server yang memakai cache konfigurasi, jalankan:

```bash
php artisan config:clear
php artisan config:cache
```

## Keamanan chatbot

Chatbot hanya menerima pesan teks; riwayat delapan pesan terakhir berasal dari sesi server dan tidak dipakai lagi setelah 30 menit tidak aktif. Browser tidak dapat memasok riwayat model, instruksi sistem, alat, atau tujuan API. Gunakan session/cache server seperti database atau Redis yang mendukung atomic locks; semua instance hosting harus berbagi penyimpanan cache untuk kuota global.

Batas bawaan: 10 permintaan/menit/IP, 60/jam/IP, 30/menit seluruh aplikasi, dan 1.000/24 jam seluruh aplikasi. AI_HOURLY_LIMIT mengatur batas per jam/IP dan AI_DAILY_LIMIT mengatur batas global per 24 jam. Batas menghitung permintaan aplikasi, termasuk yang tidak valid; retry dapat membuat maksimal dua panggilan Gemini per permintaan. AI_ENABLED=false menonaktifkan chatbot tanpa menonaktifkan pelaporan.

Model tidak diberi akses database, berkas server, alat eksekusi, atau tindakan CRUD. Koneksi dibatasi ke HTTPS API Google dan tidak mengikuti redirect. Pola NIK/kredensial tertentu disamarkan; penyamaran ini tidak menjamin semua data pribadi terdeteksi. Jawaban ditampilkan sebagai teks, bukan HTML/Markdown aktif. Prompt injection tetap dapat memengaruhi isi jawaban, sehingga draf harus diperiksa warga sebelum dikirim. Ganti API key yang pernah dibagikan.

## Peran dan akses

### Pelapor

- Membuat laporan tanpa akun dan tanpa NIK.
- Menggunakan Asisten AI untuk bertanya atau menyusun deskripsi.
- Melihat status laporan miliknya melalui token tamu.
- Memberikan rating dan komentar setelah laporan selesai.

### Admin

- Melihat dan menangani seluruh laporan.
- Mencatat laporan dari kanal online/offline.
- Memajukan status sampai ditindaklanjuti dan menyelesaikan laporan.
- Mengelola kegiatan internal, evaluasi, arsip, dan melihat feedback.

### Super Admin

- Melihat dashboard, statistik, serta peta persebaran laporan dan kegiatan.
- Melihat detail laporan secara baca-saja.
- Mengelola akun Admin dan data petugas.
- Mengelola kegiatan internal dan memantau audit aktivitas.
- Tidak mengelola unit organisasi atau wilayah operasional.

## Alur laporan

```text
Pelapor mengirim laporan
  → submitted
  → received
  → verifying
  → in_progress
  → completed
  → pelapor dapat memberi feedback
```

Penyelesaian laporan membuat satu kegiatan eksternal, mencatat petugas yang ditugaskan, hasil penanganan, lokasi, dan dokumentasi.

## Pengujian

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

Dokumentasi rinci, ERD, diagram kelas, sequence, dan use case tersedia di direktori [`docs`](docs).
