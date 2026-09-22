# Brief SIPRAJA — Spesifikasi As-Built

Versi dokumen: 21 September 2026

## Tujuan

SIPRAJA mendukung tiga kebutuhan utama Satpol PP Kulon Progo:

1. penerimaan dan penanganan laporan masyarakat;
2. pencatatan kegiatan, evaluasi, arsip, dan petugas;
3. pengawasan melalui dashboard, peta persebaran, notifikasi, dan audit aktivitas.

## Aktor

### Pelapor

Pelapor adalah pengguna tamu tanpa akun. Pelapor dapat mengirim laporan, memakai Asisten AI, melihat status laporan miliknya, dan memberi feedback setelah selesai.

### Admin

Admin adalah pelaksana operasional. Admin melihat dan menangani laporan, membuat laporan dari kanal lain, menyelesaikan laporan, serta mengelola kegiatan, evaluasi, arsip, dan feedback.

### Super Admin

Super Admin melakukan pengawasan dan administrasi akun/petugas. Peta persebaran berada langsung pada dashboard. Super Admin tidak mengubah status laporan dan tidak mengelola struktur organisasi/wilayah.

## Laporan masyarakat

### Jenis laporan

Hanya ada satu jenis, yaitu **Lapor** (`lapor`). Pilihan Darurat dan Informasi & Pengaduan sudah dihapus.

### Data form publik

- Nomor WhatsApp/telepon: wajib.
- Nama pelapor: opsional.
- NIK: tidak ditampilkan dan tidak diminta.
- Deskripsi kejadian: wajib.
- Alamat/lokasi: wajib sesuai validasi aplikasi.
- Koordinat: digunakan bila pelapor menentukan titik peta.
- Foto: opsional sesuai batas format, jumlah, dan ukuran aplikasi.

Kolom NIK masih tersedia secara nullable di database hanya untuk kompatibilitas data lama.

### Asisten AI

Pada halaman Lapor, warga dapat:

- bertanya cara melapor;
- menjelaskan kejadian secara bertahap;
- meminta bantuan menyusun draf deskripsi;
- memakai jawaban yang memang berupa draf sebagai isi deskripsi.

AI wajib menjawab dalam Bahasa Indonesia, menjaga konteks percakapan, tidak meminta data sensitif, tidak mengklaim laporan sudah terkirim, dan mengarahkan ancaman keselamatan langsung ke layanan darurat setempat tanpa mengarang nomor.

Riwayat delapan pesan terakhir dikelola di sesi server dan tidak digunakan setelah 30 menit tidak aktif. Payload riwayat palsu, instruksi sistem, alat, model, dan alamat API dari browser ditolak. Rate limit per IP/global, session lock, redaksi pola data sensitif, pembatasan koneksi ke Google tanpa redirect, dan validasi keluaran membatasi dampak penyalahgunaan. Jawaban tetap teks biasa dan draf memerlukan tindakan warga untuk dikirim; AI tidak memiliki akses CRUD. Pengamanan ini bukan jaminan model kebal semua prompt injection.

### Status

```text
Laporan dibuat → Laporan diterima → Sedang diverifikasi
→ Ditindaklanjuti → Selesai
```

Status bergerak satu arah. Penyelesaian membuat kegiatan eksternal yang terhubung ke laporan.

### Akses data

- Pelapor hanya melihat laporan miliknya melalui guest token.
- Admin melihat dan menangani laporan.
- Super Admin dapat melihat detail laporan secara baca-saja.
- Masyarakat umum tidak memperoleh daftar seluruh laporan.

## Dashboard Super Admin

Dashboard memuat ringkasan sistem dan peta persebaran laporan/kegiatan yang memiliki koordinat. Tidak ada menu Peta terpisah.

## Organisasi dan wilayah

Fitur Manajemen Organisasi tidak digunakan. `organizational_units` dan `operational_zones` telah dihapus. Petugas tidak memiliki foreign key ke unit organisasi atau zona operasional.

Data petugas yang tetap digunakan:

- NIP/nomor pegawai;
- nama;
- jabatan;
- nama satuan kerja;
- nama unit kerja;
- status aktif/cuti/nonaktif.

## Kegiatan

- Kegiatan internal dibuat langsung oleh Admin atau Super Admin.
- Kegiatan eksternal dibuat otomatis ketika Admin menyelesaikan laporan.
- Kegiatan dapat mempunyai template, lokasi, petugas, temuan, dokumentasi, hasil penanganan, dan evaluasi.
- Satu laporan maksimal mempunyai satu kegiatan eksternal.

## Modul pendukung

- Notifikasi laporan baru untuk Admin aktif.
- Feedback berupa rating dan komentar setelah laporan selesai.
- Arsip mendukung beberapa file per folder.
- Monitoring Super Admin membaca audit log.
- Pencarian global menyesuaikan hak akses role.

## Menu akhir

### Publik

- Beranda
- Lapor
- Status
- Feedback
- Asisten AI pada halaman Lapor

### Admin

- Dashboard
- Pengaduan/Laporan
- Acara & Kegiatan
- Evaluasi
- Arsip
- Feedback
- Notifikasi
- Profil

### Super Admin

- Dashboard + Peta Persebaran
- Kegiatan
- Manajemen User
- Petugas
- Monitoring
- Notifikasi
- Profil

Menu **Peta** terpisah dan **Manajemen Organisasi** tidak ada.

## Kriteria penerimaan utama

1. Warga dapat melapor tanpa NIK dan tanpa memilih jenis laporan.
2. Nilai `reports.type` untuk laporan baru adalah `lapor`.
3. Admin dapat menuntaskan alur laporan sampai selesai.
4. Super Admin melihat peta pada dashboard, bukan route/menu tersendiri.
5. Tidak ada UI, route, model, atau tabel aktif untuk unit organisasi dan zona operasional.
6. Asisten AI menggunakan API key dari server dan gagal secara aman bila penyedia tidak tersedia.
7. Hak akses laporan mengikuti role dan kepemilikan guest token.

## Fitur yang dihapus

- Pilihan jenis laporan lama.
- Input NIK pada form warga.
- Menu Peta terpisah.
- Manajemen Organisasi dan wilayah operasional.
- Fitur Aset, peringatan pajak/servis, dan tabel `assets`.
