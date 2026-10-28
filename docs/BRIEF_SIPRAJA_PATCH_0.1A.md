# Brief SIPRAJA — Spesifikasi As-Built

Pembaruan: 27 Oktober 2026. Acuan kode: commit `503d836` pada branch `samuel`.
Nama berkas dipertahankan untuk kompatibilitas; isi menjelaskan sistem saat ini.

## Tujuan dan cakupan

SIPRAJA membantu Satpol PP Kulon Progo menerima pengaduan masyarakat, mencatat
penanganan dan kegiatan, mengelola evaluasi serta arsip, dan memantau layanan.
Dokumen ini menjelaskan perilaku yang tersedia dalam kode, bukan rencana fitur.

## Aktor dan hak akses

| Aktor | Akses utama |
|---|---|
| Pelapor | Tanpa akun; verifikasi WhatsApp, kirim laporan, lihat status milik browser sendiri, beri penilaian, buka survei SKM, gunakan Asisten AI. |
| Admin aktif | Melihat laporan pada tahun terpilih, mencatat laporan kanal lain, menangani laporan yang ditugaskan kepadanya, mengelola kegiatan internal, evaluasi, arsip, dan membaca feedback. |
| Super Admin aktif | Dashboard dan peta, daftar/detail pengaduan, penugasan Admin, kegiatan internal dan ekspor, akun Admin, direktori pegawai e-Profil, serta monitoring audit. |

Semua Admin dapat membaca laporan pada tahun terpilih. Perubahan status,
penolakan, penyelesaian, dan penghapusan laporan hanya boleh dilakukan Admin
aktif yang menjadi penanggung jawab. Evaluasi kegiatan eksternal mengikuti
penugasan laporan asal; evaluasi kegiatan internal tersedia untuk Admin.
Super Admin mengatur penugasan tetapi tidak memproses status/penyelesaian laporan.

## Tahun data

- Dialog **Pilih Tahun Data** muncul bila sesi internal belum memiliki pilihan.
- Tombol **Data [tahun]** di bagian atas membuka kembali pemilih tahun.
- Pilihan disimpan pada sesi melalui `PUT /data-year`; setelah memilih, pengguna
  kembali ke dashboard perannya.
- Laporan dan feedback mengikuti tahun `reports.created_at`; kegiatan dan
  evaluasi mengikuti tahun `activities.activity_date`; arsip dan audit mengikuti
  `created_at`. Notifikasi internal juga dibatasi tahun pembuatannya.
- Pencarian global, peta, detail, lampiran, dan ekspor mengikuti cakupan tahun.
  Membuka detail data dari tahun lain menghasilkan 404 sampai tahun dipilih.
- Pembuatan laporan oleh Admin dan arsip baru hanya diizinkan pada tahun berjalan.
  Kegiatan internal dapat dicatat pada tahun terpilih; tanggal penyelesaian
  laporan harus dalam tahun terpilih dan tidak melampaui hari ini.
- Daftar akun dan direktori e-Profil tidak dibagi berdasarkan tahun.
  Pelacakan laporan warga tetap mengikuti token browser, tanpa pemilih tahun.

## Pelaporan warga dan OTP

Hanya ada satu jenis laporan, **Lapor** (`lapor`). Form publik meminta nomor
WhatsApp, deskripsi maksimal 5.000 karakter, alamat maksimal 1.000 karakter,
persetujuan kebenaran data, dan foto opsional. Nama, NIK, serta koordinat tidak
diminta. Koordinat laporan publik awalnya kosong. Kolom `reports.reporter_nik`
sudah dihapus oleh migrasi 19 Oktober 2026.

Foto maksimal tiga berkas JPG/JPEG/PNG/WEBP, masing-masing 5 MB. Nomor WhatsApp
harus diverifikasi pada sesi browser yang sama sebelum pengiriman laporan.
OTP enam angka berlaku lima menit, jeda kirim ulang 60 detik, dengan batas lima
percobaan salah sebelum kode tidak dapat digunakan lagi. Verifikasi berhasil
berlaku 15 menit pada sesi dan nomor tersebut. Permintaan OTP dibatasi per IP
dan per nomor; pengiriman gagal mengembalikan pesan kegagalan.

Setelah laporan tersimpan, sistem membuat kode `SPJ-XXXXXX`, riwayat awal,
lampiran, dan notifikasi database untuk Admin serta Super Admin aktif. Laporan
warga juga memicu WhatsApp konfirmasi kepada pelapor dan pemberitahuan kepada
Admin aktif yang memiliki nomor telepon. Token mentah hanya berada pada cookie
HttpOnly `sipraja_guest_token`; database menyimpan hash dan kedaluwarsanya.
Kode laporan saja tidak memberi akses dari browser/perangkat lain.

## Penugasan dan penanganan laporan

1. Super Admin membuka **Manajemen Pengaduan** lalu detail laporan pada tahun
   yang sesuai.
2. Pada **Penanggung Jawab**, pilih Admin aktif dan tekan **Simpan Penugasan**.
   Pilihan **Belum ditugaskan** mengosongkan penugasan. Perubahan dicatat ke audit.
3. Admin yang ditugaskan memproses satu tahap setiap kali:

   ```text
   submitted → received → verifying → in_progress → completed
                             └───────────────→ rejected
   ```

   Penolakan hanya dari `verifying` dan membutuhkan alasan maksimal 1.000 karakter.
   Dari `in_progress`, penyelesaian dilakukan melalui formulir khusus.
4. Penyelesaian mensyaratkan nama kegiatan, tanggal dalam tahun terpilih dan
   paling lambat hari ini, hasil penanganan, alamat, koordinat, minimal satu
   pegawai e-Profil, serta satu sampai tiga foto penanganan (5 MB per foto).
5. Transaksi membuat satu kegiatan eksternal, referensi NIP personel, temuan
   opsional, lampiran, riwayat status, waktu selesai, dan audit. Satu laporan
   maksimal memiliki satu kegiatan eksternal, termasuk yang telah dihapus lunak.
6. Pembaruan status/penolakan dikirim lewat WhatsApp. Pesan penyelesaian memuat
   hasil penanganan dan tautan survei SKM. Kegagalan notifikasi dicatat ke log dan
   tidak membatalkan laporan atau perubahan status yang telah tersimpan.

Laporan yang dicatat Admin dari kanal Online/Offline juga berawal tanpa
penugasan, termasuk bila pembuatnya Admin. Form ini memerlukan koordinat dan
nomor telepon, mengizinkan nama opsional, dan tidak memakai OTP maupun NIK.
Laporan tersebut tidak otomatis terikat pada token browser warga.

## Kegiatan, evaluasi, dan ekspor

Admin dan Super Admin dapat mengelola kegiatan internal. Kegiatan eksternal
dibuat melalui penyelesaian laporan. Kegiatan menyimpan kategori/template,
tanggal/waktu, lokasi, hasil, personel, maksimal 50 baris temuan, dan dokumentasi.
Dokumentasi internal opsional dengan total maksimal tiga foto (5 MB per berkas).

Ekspor **Excel (.xlsx)**, **PDF (.pdf)**, dan **Word (.docx)** tersedia pada daftar
kegiatan untuk kedua peran. Seluruh hasil filter diekspor, termasuk halaman
tabel lain, dalam tahun terpilih. PDF dan Word memuat dokumentasi kegiatan serta
bukti publik laporan terkait; foto disusun dalam kolom dokumentasi dengan
proporsi gambar dipertahankan. Excel mencantumkan nama berkas dokumentasi.
Berkas gambar diambil melalui route internal yang memerlukan autentikasi.

Admin mengelola evaluasi berupa hasil maksimal 3.000 karakter dan kendala
opsional maksimal 3.000 karakter. Satu kegiatan mempunyai satu data evaluasi yang
dapat diperbarui atau dihapus sesuai otorisasi.

## Pegawai e-Profil

**Manajemen Petugas** menampilkan **Daftar Pegawai** secara baca-saja dari API
e-Profil: nama, NIP, dan jabatan. SIPRAJA tidak menyediakan tambah/edit/hapus
pegawai lokal. Data induk diperbarui pada e-Profil.

`EprofilService` mengambil data dengan `api_eprofil` dan `token_eprofil` dari
konfigurasi server. Data dipakai ulang dalam request yang sama, bukan disalin
menjadi tabel master pegawai lokal. `activity_personnel` hanya menyimpan
`activity_id`, `employee_nip`, peran penugasan, dan catatan; pasangan kegiatan/NIP
unik. NIP bukan foreign key ke database e-Profil. Tabel `officers` dan
`activity_officer` sudah diganti melalui migrasi 5 Oktober 2026.

Bila API gagal, daftar kosong dengan peringatan. Personel historis yang tak
ditemukan ditampilkan sebagai **Pegawai tidak tersedia**. Saat mengedit kegiatan,
NIP yang sudah terpasang tetap boleh dipertahankan.

## Modul pendukung dan menu

- Publik: Beranda, Lapor, Status, Survey Kepuasan; Asisten AI pada halaman Lapor.
- Admin: Dashboard, Manajemen Pengaduan, Acara & Kegiatan, Evaluasi Kegiatan,
  Feedback Masyarakat, Arsip, profil, notifikasi, pencarian, dan tahun data.
- Super Admin: Dashboard dengan peta persebaran, Manajemen Pengaduan, Kegiatan,
  Manajemen User, Manajemen Petugas, Monitoring Sistem, profil, notifikasi,
  pencarian, dan tahun data.
- Penilaian per laporan: satu kali setelah selesai, rating 1–5, komentar opsional
  maksimal 2.000 karakter. Survei SKM eksternal merupakan layanan terpisah.
- Arsip: folder dengan maksimal lima file, 20 MB per file, kategori dan sifat
  dokumen. Akses berkas melalui route terotorisasi.
- Akun Admin: nama, username, email opsional, nomor WhatsApp opsional, password,
  blokir/aktifkan, dan hapus sesuai pembatasan aplikasi. Profil sendiri juga
  mendukung nomor WhatsApp. Akun pegawai e-Profil tidak otomatis menjadi akun login.
- Peta hanya menampilkan laporan/kegiatan berkoordinat dan dimuat ulang bersama
  halaman; label Live bukan jaminan pembaruan tanpa memuat ulang.

## Asisten AI dan integrasi

`POST /lapor/asisten-ai` hanya menerima `message` maksimal 600 karakter. Riwayat
delapan pesan terakhir dikelola server pada sesi, kedaluwarsa setelah 30 menit
tidak aktif. Payload `history`, instruksi sistem, alat, model, atau alamat API
dari browser dilarang. Jawaban berupa teks biasa, bukan tindakan CRUD/pengiriman.

Konfigurasi Gemini: `AI_ENABLED`, `AI_API_KEY`, `AI_MODEL`, `AI_BASE_URL`,
`AI_HOURLY_LIMIT`, dan `AI_DAILY_LIMIT`. Nilai default model **dalam kode** adalah
`gemini-3.5-flash`; ini bukan pernyataan ketersediaan model pada akun penyedia.
Batas bawaan: 10 permintaan/IP/menit, 30 global/menit, 60/IP/jam, dan 1.000
global/hari. Ada session lock, redaksi pola sensitif, timeout, retry terbatas,
validasi keluaran, dan pesan aman ketika penyedia gagal atau AI dinonaktifkan.

WhatsApp memakai layanan lokal `wa-bot/server.js`, `WHATSAPP_BOT_URL`, dan
`WHATSAPP_BOT_TOKEN` di Laravel. Pada proses bot, token bernama `WA_BOT_TOKEN`
dan harus cocok; `PORT` bawaan 3456, `CHROME_PATH` opsional. Bot memakai sesi
WhatsApp Web yang harus terhubung. Tautan survei dibaca dari `SKM_SURVEY_URL`.
Kredensial sebenarnya tidak disimpan dalam dokumentasi ini.

## Komponen yang sudah dihapus

Pilihan Darurat/Informasi & Pengaduan, seluruh kolom/input NIK laporan, master
pegawai lokal, organisasi/zona operasional, menu Peta terpisah, serta fitur/tabel
Aset tidak termasuk sistem aktif. Berkas migrasi lama tetap ada sebagai riwayat.

## Sumber pemeriksaan

`routes/web.php` (74 route aplikasi saat diperiksa), `app/Providers/AppServiceProvider.php`,
controller dan request terkait, `app/Support/DataYear.php`, `app/Services`,
model dan migrasi sampai 19 Oktober 2026, Blade, `public/js/activity-word-export.js`,
serta pengujian fitur pada `tests/Feature`. Pemeriksaan ini tidak menyatakan
bahwa integrasi eksternal sudah diuji langsung pada layanan produksi.
