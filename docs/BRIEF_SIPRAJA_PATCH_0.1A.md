# BRIEF SISTEM SIPRAJA — PATCH 0.1A

## 1. Konsep Sistem

**SIPRAJA** adalah sistem informasi internal Satpol PP yang memiliki 3 fokus utama:

1. **Pengaduan masyarakat**
2. **Kegiatan dan operasional Satpol PP**
3. **Monitoring dan rekap kinerja**

Prinsip utama:

> **Simple — Aplikatif — Informatif**

Sistem harus sederhana untuk masyarakat, tetapi cukup lengkap untuk kebutuhan internal Satpol PP.

---

## 2. Aktor Sistem

### A. Pelapor / Masyarakat

Tidak perlu membuat akun untuk mengirim laporan.

Alur:

```text
Buka SIPRAJA
    ↓
Pilih jenis laporan
    ↓
Isi nomor WhatsApp/telepon
    ↓
Isi deskripsi singkat
    ↓
Upload foto/video
    ↓
Isi lokasi kejadian
    ↓
Kirim laporan
    ↓
Mendapat nomor/kode laporan
    ↓
Pelapor dapat melihat status laporannya
```

#### Identitas Pelapor

- **Nomor telepon/WhatsApp wajib**
- **NIK opsional**
- Tidak perlu registrasi akun
- Nomor telepon digunakan untuk validasi dan komunikasi tindak lanjut
- Jangan membuat proses pelaporan terlalu panjang

#### Data laporan minimal

- Nomor laporan
- Nomor telepon pelapor
- NIK (opsional)
- Deskripsi singkat
- Foto/video
- Lokasi kejadian
- Waktu laporan
- Kategori laporan
- Status laporan

---

## 3. Jenis Laporan

Jangan gunakan istilah:

```text
Urgent
Tidak Urgent
```

Gunakan:

### Fitur Darurat

Untuk kejadian yang membutuhkan respons cepat.

Data tambahan:

- Identitas pelapor
- Nomor WhatsApp/telepon yang dapat dihubungi
- Deskripsi kejadian
- Foto/video
- Lokasi kejadian

### Informasi & Pengaduan

Untuk laporan yang tidak membutuhkan respons darurat.

Data dibuat sesederhana mungkin:

```text
Nomor HP
Deskripsi
Foto/video
Lokasi
```

---

## 4. Validasi Laporan

Masalah utama:

Laporan dari media sosial/WhatsApp sering tidak valid atau sudah terlambat ketika petugas datang.

Contoh:

```text
Masyarakat melapor kejadian
        ↓
Petugas menerima laporan
        ↓
Petugas datang ke lokasi
        ↓
Kejadian/orang sudah tidak ada
```

Karena itu sistem harus menyimpan:

- Nomor telepon pelapor
- NIK jika tersedia
- Foto/video
- Lokasi kejadian
- Waktu laporan

Tujuannya agar laporan lebih mudah diverifikasi dan dapat dihubungi kembali.

---

## 5. Status Laporan

Status harus ditampilkan secara bertahap seperti sistem tracking Gojek/Shopee.

Contoh:

```text
Laporan dibuat
      ↓
Laporan diterima
      ↓
Sedang diverifikasi
      ↓
Ditindaklanjuti
      ↓
Petugas menuju lokasi
      ↓
Penanganan selesai
      ↓
Laporan selesai
```

Status harus bisa dilihat oleh pelapor.

Pelapor:

- Hanya dapat melihat laporan miliknya sendiri
- Dapat melihat status laporan
- Dapat melihat riwayat/rekap laporan miliknya

---

## 6. Feedback Masyarakat

Setelah laporan selesai, tampilkan:

```text
Bagaimana kepuasan Anda terhadap layanan kami?
```

Sediakan link/form **kepuasan masyarakat**.

---

## 7. Statistik Pengaduan

Sistem harus menyediakan statistik internal.

Contoh:

- Jumlah laporan masuk
- Jumlah laporan selesai
- Jumlah laporan yang sedang ditindaklanjuti
- Jumlah laporan berdasarkan kategori
- Jumlah laporan berdasarkan periode
- Persentase penyelesaian laporan

Contoh indikator:

```text
Tingkat penyelesaian laporan =
Laporan selesai / total laporan × 100%
```

---

## 8. Admin

Admin dapat berjumlah banyak.

Admin dibagi berdasarkan bidang/bagian.

Contoh:

```text
Admin Bidang A
Admin Bidang B
Admin Bidang C
```

Admin bertugas:

- Melihat laporan
- Memverifikasi laporan
- Mengubah status laporan
- Menindaklanjuti laporan
- Menginput kegiatan
- Melihat statistik
- Membuat rekap

### Notifikasi laporan

Jika terdapat laporan yang belum ditindaklanjuti:

```text
MERAH → belum ditindaklanjuti
HIJAU → sudah ditindaklanjuti/selesai
```

Bisa menggunakan popup/notifikasi pada dashboard admin.

---

## 9. Penugasan Petugas

Untuk PATCH 0.1A:

**Tidak perlu memilih personnel secara manual ketika membuat penugasan.**

Sistem cukup membuat tugas/penugasan.

Monitoring perjalanan petugas/GPS:

> **SKIP DULU**

Jangan dibuat pada versi ini.

---

## 10. Kegiatan & Acara

Admin dapat menginput kegiatan Satpol PP.

Ketika menu:

```text
Acara & Kegiatan
```

dibuka, tampilkan:

- Nama kegiatan
- Tanggal
- Waktu
- Lokasi
- Kategori
- Deskripsi
- Dokumentasi/foto

Contoh kategori:

- Patroli
- Pengawalan
- Ketertiban umum
- Keamanan
- Kegiatan lainnya

---

## 11. Statistik Kegiatan

Sistem harus dapat menampilkan grafik kegiatan.

Contoh pertanyaan yang harus bisa dijawab sistem:

> "Berapa banyak patroli yang dilakukan selama satu bulan?"

Contoh statistik:

```text
Patroli       : 25
Pengawalan    : 12
Ketertiban    : 18
Keamanan      : 15
```

Data kegiatan harus dapat direkap berdasarkan:

- Hari
- Minggu
- Bulan
- Tahun
- Kategori kegiatan

---

## 12. Data Internal Satpol PP

Data internal adalah salah satu poin penting sistem.

Sistem tidak hanya menjadi aplikasi pengaduan masyarakat.

Sistem juga menyimpan:

```text
PENGADUAN
+
KEGIATAN
+
KINERJA
+
DATA INTERNAL
```

Sehingga seluruh data dapat digunakan untuk statistik dan evaluasi.

---

## 13. Personnel / SDM

Data personnel boleh ditampilkan secara sederhana.

Tidak perlu menampilkan:

- Riwayat resign
- Riwayat pensiun
- Informasi personnel terlalu detail

Informasi personnel yang aman dapat digunakan untuk kebutuhan internal.

---

## 14. Evaluasi Kinerja

Evaluasi tetap digunakan.

Tambahkan data:

- Presensi kedatangan
- Kegiatan Satpol PP
- Rekap laporan
- Aktivitas/kinerja

Admin bertugas memasukkan indikator masing-masing.

Super Admin tidak perlu memasukkan semua data secara manual.

---

## 15. Super Admin

Super Admin memiliki akses lebih tinggi.

Fokus Super Admin:

- Mengelola organisasi
- Mengelola user/admin
- Melihat data internal
- Menarik data/statistik
- Melihat evaluasi
- Menindaklanjuti hal yang membutuhkan perhatian
- Melihat rekap kegiatan
- Melihat rekap pengaduan

Super Admin lebih banyak **monitoring dan mengambil tindakan**, bukan menginput seluruh data.

---

## 16. Dashboard Super Admin

Dashboard harus informatif tetapi sederhana.

Contoh:

```text
TOTAL PENGADUAN
125

SELESAI
98

DALAM PROSES
20

BELUM DITINDAKLANJUTI
7
```

Kemudian grafik:

```text
Statistik Pengaduan
Statistik Kegiatan
Statistik Kategori
Statistik Kinerja
```

Contoh kategori pengaduan/kegiatan:

- Pengawalan
- Ketertiban umum
- Keamanan
- Patroli
- Kategori lainnya

---

## 17. Landing Page

Landing page harus sederhana.

Jangan menampilkan terlalu banyak informasi internal.

Fokus masyarakat:

```text
SIPRAJA

LAPOR DARURAT
INFORMASI & PENGADUAN
CEK STATUS LAPORAN
KEPUASAN MASYARAKAT
```

Untuk:

```text
Manajemen Organisasi
SDM
Evaluasi
Data internal
```

tidak perlu ditampilkan di landing page.

---

## 18. Prinsip UI/UX

Prioritas:

### SIMPLE

Masyarakat dapat membuat laporan dengan cepat.

### APLIKATIF

Fitur benar-benar sesuai kebutuhan Satpol PP.

### INFORMATIF

Admin dan Super Admin mendapatkan statistik dan informasi yang jelas.

Hindari:

- Registrasi yang panjang
- Form terlalu banyak
- Informasi yang tidak diperlukan
- Fitur GPS petugas untuk sementara
- Tampilan terlalu kompleks

---

## 19. Alur Besar Sistem

```text
                    SIPRAJA
                       │
          ┌────────────┴────────────┐
          │                         │
     MASYARAKAT                 INTERNAL
          │                         │
          ↓                         ↓
   LAPOR DARURAT             ADMIN / SUPER ADMIN
          │                         │
   INFORMASI &                 ┌────┴─────┐
   PENGADUAN                   │          │
          │                PENGADUAN   KEGIATAN
          ↓                   │          │
     NOMOR LAPORAN             └────┬─────┘
          │                         │
          ↓                         ↓
     CEK STATUS              STATISTIK & REKAP
          │                         │
          ↓                         ↓
      FEEDBACK                 EVALUASI
```

---

## 20. Prioritas PATCH 0.1A

### PRIORITAS TINGGI

1. Landing page
2. Laporan tanpa registrasi akun
3. Nomor telepon wajib
4. NIK opsional
5. Darurat
6. Informasi & Pengaduan
7. Upload foto/video
8. Lokasi kejadian
9. Nomor/kode laporan
10. Tracking status laporan
11. Dashboard Admin
12. Dashboard Super Admin
13. Rekap pengaduan
14. Rekap kegiatan
15. Statistik/grafik

### PRIORITAS RENDAH / NANTI

1. Tracking GPS petugas
2. Riwayat resign
3. Riwayat pensiun
4. Data personnel terlalu detail
5. Fitur kompleks lainnya

---

## 21. Aturan Penting untuk AI Developer

Saat mengembangkan SIPRAJA:

- Jangan membuat fitur di luar kebutuhan tanpa alasan.
- Utamakan kesederhanaan.
- Jangan membuat registrasi akun untuk masyarakat.
- Nomor telepon adalah identitas utama pelapor.
- NIK bersifat opsional.
- Foto/video menjadi bagian penting validasi laporan.
- Status laporan harus bertahap dan jelas.
- Data pengaduan dan kegiatan harus dapat direkap.
- Dashboard harus menggunakan statistik/grafik yang mudah dipahami.
- Pisahkan hak akses **Pelapor, Admin, dan Super Admin**.
- Monitoring GPS petugas belum dibuat.
- Jangan menampilkan data internal secara berlebihan di landing page.
- Nama sistem yang digunakan sementara: **SIPRAJA**.
- Saat aplikasi dibuka, tampilkan **nama sistem saja**, tidak perlu nama kepemilikan website.
