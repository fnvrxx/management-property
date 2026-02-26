# Panduan Penggunaan — Property Management System

> Dokumen ini menjelaskan cara mengoperasikan sistem secara langkah demi langkah.

---

## Daftar Isi

1. [Login ke Sistem](#1-login-ke-sistem)
2. [Mengelola Properti](#2-mengelola-properti)
3. [Mengelola Data Penyewa](#3-mengelola-data-penyewa)
4. [Membuat Kontrak Sewa](#4-membuat-kontrak-sewa)
5. [Mengelola Invoice](#5-mengelola-invoice)
6. [Mencatat Riwayat Pembayaran](#6-mencatat-riwayat-pembayaran)
7. [Generate Invoice Otomatis](#7-generate-invoice-otomatis)
8. [Export Data ke Excel](#8-export-data-ke-excel)
9. [Memahami Dashboard](#9-memahami-dashboard)
10. [Alur Kerja Lengkap](#10-alur-kerja-lengkap)

---

## 1. Login ke Sistem

1. Buka browser, akses `http://localhost:8000/admin`
2. Masukkan **Email** dan **Password**
3. Klik tombol **Sign in**

> **Akun demo:** `admin@property.test` / `password`

---

## 2. Mengelola Properti

Menu: **Sidebar → Properties**

### 2.1 Menambah Properti Baru

1. Klik tombol **New Property** (pojok kanan atas)
2. Isi form berikut:

| Field | Cara Mengisi | Contoh |
|---|---|---|
| **Kode Lokasi** | Kode unik, tidak boleh sama dengan properti lain | `BLG-A01` |
| **Nama** | Nama lengkap properti | `Gedung A Lantai 1` |
| **Status** | Pilih dari dropdown | `Tersedia` |
| **Catatan** | Keterangan tambahan (opsional) | `Luas 120m²` |

3. Klik **Save**

> **Catatan:** Status akan otomatis berubah ke **Disewa** ketika kontrak sewa dibuat untuk properti tersebut.

### 2.2 Mengubah Status Properti

1. Klik ikon **Edit** (pensil) pada baris properti
2. Ubah field **Status**
3. Klik **Save**

**Pilihan status:**

| Status | Arti |
|---|---|
| `Tersedia` | Properti kosong, siap disewa |
| `Disewa` | Sedang dihuni penyewa aktif |
| `Maintenance` | Sedang dalam perbaikan |

---

## 3. Mengelola Data Penyewa

Menu: **Sidebar → Penyewa**

### 3.1 Menambah Penyewa Baru

1. Klik tombol **New Penyewa**
2. Isi form:

| Field | Cara Mengisi | Wajib |
|---|---|---|
| **Nama** | Nama lengkap / nama perusahaan | Ya |
| **Kontak** | Nomor telepon yang bisa dihubungi | Ya |
| **Email** | Alamat email (format: `nama@domain.com`) | Tidak |
| **Alamat** | Alamat lengkap penyewa | Tidak |

3. Klik **Save**

### 3.2 Mencari Penyewa

Gunakan kotak **Search** di atas tabel. Pencarian berjalan pada kolom **Nama** dan **Email**.

### 3.3 Menghapus Penyewa

Klik ikon **Delete** (tempat sampah) pada baris penyewa, lalu konfirmasi penghapusan.

> **Perhatian:** Menghapus penyewa akan menghapus semua kontrak dan invoice terkait (cascade delete).

---

## 4. Membuat Kontrak Sewa

Menu: **Sidebar → Kontrak Sewa**

### 4.1 Membuat Kontrak Baru

1. Klik tombol **New Kontrak**
2. Isi form bagian **Informasi Dasar:**

| Field | Cara Mengisi | Wajib |
|---|---|---|
| **Penyewa** | Pilih dari dropdown (ketik untuk mencari) | Ya |
| **Properti** | Pilih kode lokasi dari dropdown | Ya |
| **Tanggal Mulai** | Klik kalender, pilih tanggal | Ya |
| **Tanggal Akhir** | Klik kalender, pilih tanggal | Ya |
| **Periode Sewa** | Tuliskan deskripsi periode | Ya |
| **Harga Sewa / Bulan** | Nominal angka saja (tanpa titik/koma) | Ya |
| **PPN (%)** | Persentase PPN, default sudah 11% | Ya |
| **PPB (%)** | Persentase PPB, default 0% | Tidak |
| **Catatan** | Keterangan tambahan kontrak | Tidak |

> **Tip:** Setelah memilih properti, status properti tersebut otomatis berubah menjadi **Disewa**.

### 4.2 Menambahkan Tagihan Lainnya

Tagihan lainnya adalah biaya tambahan di luar harga sewa (misal: listrik, keamanan, IPL).

1. Scroll ke bawah form, temukan bagian **Tagihan Lainnya**
2. Klik tombol **Add Tagihan Lainnya**
3. Isi setiap baris:

| Field | Cara Mengisi | Contoh |
|---|---|---|
| **Nama Tagihan** | Nama jenis tagihan | `Listrik` |
| **Jumlah** | Nominal dalam Rupiah | `200000` |

4. Ulangi untuk tagihan lain jika ada
5. Untuk menghapus baris, klik tombol `×` di kanan

### 4.3 Formula Perhitungan Invoice

Sistem akan otomatis menghitung total invoice berdasarkan:

```
Total = Harga Sewa
      + (Harga Sewa × PPN%)
      + (Harga Sewa × PPB%)
      + Sum(Semua Tagihan Lainnya)
```

**Contoh:**
- Harga Sewa: Rp 10.000.000
- PPN 11%: Rp 1.100.000
- Listrik: Rp 200.000
- **Total Invoice: Rp 11.300.000**

---

## 5. Mengelola Invoice

Menu: **Sidebar → Invoice**

Invoice dibuat secara **otomatis** oleh sistem setiap bulan. Tugas Anda hanya memperbarui status pembayarannya.

### 5.1 Memperbarui Status Pembayaran

1. Klik ikon **Edit** (pensil) pada baris invoice
2. Di bagian atas, lihat informasi read-only:
   - **Penyewa** — nama penyewa
   - **Lokasi** — kode properti
   - **Bulan/Tahun** — periode tagihan
   - **Jumlah Tagihan** — total yang harus dibayar
   - **Jatuh Tempo** — batas waktu pembayaran
   - **Total Terbayar** — jumlah yang sudah masuk via riwayat pembayaran
   - **Sisa Tagihan** — sisa yang belum terbayar

3. Ubah **Status Pembayaran:**

| Status | Pilih Jika |
|---|---|
| `Belum Bayar` | Invoice belum dibayar sama sekali |
| `Lunas` | Invoice sudah dibayar penuh |
| `Terlambat` | Sudah lewat jatuh tempo, belum bayar |

4. Jika status = **Lunas**, isi **Tanggal Bayar** (field akan muncul otomatis)
5. Isi **Catatan Pembayaran** jika diperlukan (misal: nomor referensi transfer)
6. Klik **Save**

### 5.2 Filter Invoice

Gunakan filter di atas tabel untuk menyaring:
- **Status** — tampilkan hanya Lunas / Belum Bayar / Terlambat
- **Jatuh Tempo 1 Bulan** — tampilkan invoice yang akan jatuh tempo dalam 30 hari

### 5.3 Indikator Sisa Hari

Kolom **Sisa Hari** menunjukkan sisa waktu hingga jatuh tempo:

| Warna | Arti |
|---|---|
| Abu-abu | Lebih dari 3 hari, atau sudah Lunas (menampilkan `-`) |
| Kuning | Tinggal 1–3 hari lagi |
| Merah | Sudah melewati jatuh tempo (nilai negatif) |

---

## 6. Mencatat Riwayat Pembayaran

Fitur ini untuk mencatat pembayaran yang **tidak sekaligus lunas** — misalnya penyewa membayar DP terlebih dahulu, lalu melunasi sisanya secara bertahap.

### 6.1 Menambah Catatan Pembayaran

1. Buka halaman edit invoice (`/admin/invoices/{id}/edit`)
2. Scroll ke bawah, temukan tabel **Riwayat Pembayaran**
3. Klik tombol **Tambah Pembayaran**
4. Isi form:

| Field | Cara Mengisi | Wajib |
|---|---|---|
| **Jenis Pembayaran** | Pilih: DP / Termin / Pelunasan | Ya |
| **Jumlah Dibayar** | Nominal yang diterima kali ini | Ya |
| **Tanggal Bayar** | Tanggal uang diterima | Ya |
| **Metode Pembayaran** | Transfer Bank / Tunai / Cek/Giro / QRIS | Tidak |
| **No. Referensi** | Kode transaksi dari bank/e-wallet | Tidak |
| **Catatan** | Keterangan tambahan | Tidak |

5. Klik **Save**

### 6.2 Jenis Pembayaran

| Jenis | Warna Badge | Kapan Digunakan |
|---|---|---|
| `DP` | Kuning | Uang muka di awal periode sewa |
| `Termin` | Biru | Cicilan per tahap |
| `Pelunasan` | Hijau | Pembayaran penutup/pelunasan penuh |

### 6.3 Cara Membaca Total Terbayar & Sisa

Di form invoice bagian atas, terdapat dua field otomatis:
- **Total Terbayar** — akumulasi semua riwayat pembayaran untuk invoice ini
- **Sisa Tagihan** — `Jumlah Tagihan − Total Terbayar`

**Contoh skenario:**
- Jumlah Tagihan: Rp 11.300.000
- Bayar DP (1 Feb): Rp 5.000.000 → Sisa: Rp 6.300.000
- Bayar Termin (15 Feb): Rp 3.000.000 → Sisa: Rp 3.300.000
- Pelunasan (28 Feb): Rp 3.300.000 → Sisa: Rp 0

Setelah lunas sepenuhnya, jangan lupa ubah **Status Pembayaran** ke `Lunas`.

---

## 7. Generate Invoice Otomatis

Invoice bulanan dibuat menggunakan **Artisan Command** dari terminal:

```bash
php artisan invoices:generate
```

### Cara Kerja

1. Sistem mencari semua kontrak yang **masih aktif** (tanggal mulai ≤ hari ini ≤ tanggal akhir)
2. Untuk tiap kontrak, sistem mengecek apakah invoice bulan ini sudah ada
3. Jika **belum ada**, invoice baru dibuat dengan status `Belum Bayar`
4. Jika **sudah ada**, dilewati (tidak duplikat)

### Penjadwalan Otomatis (Opsional)

Tambahkan ke **crontab** server agar berjalan otomatis setiap tanggal 1 pukul 00:00:

```
0 0 1 * * cd /path/ke/proyek && php artisan invoices:generate
```

---

## 8. Export Data ke Excel

### 8.1 Export dari Halaman Invoice

Di halaman daftar invoice (`/admin/invoices`), terdapat 3 tombol export di pojok kanan atas:

| Tombol | Isi File |
|---|---|
| **Export Semua** | Semua invoice di database |
| **Export 2026** | Invoice tahun berjalan saja |

### 8.2 Export Invoice Terpilih

1. Centang checkbox di baris kiri tabel (satu atau beberapa invoice)
2. Klik dropdown **Actions** (muncul setelah ada yang dicentang)
3. Pilih **Export Terpilih (.xlsx)**
4. File akan otomatis terunduh

### 8.3 Isi File Excel

File yang diunduh memiliki **2 sheet:**

**Sheet 1 — Data Invoice:**

Berisi daftar lengkap dengan kolom: No, Penyewa, Lokasi, Bulan/Tahun, Jatuh Tempo, Jumlah Tagihan, Status, Tanggal Bayar, Catatan.

**Sheet 2 — Ringkasan:**

| Keterangan | Jumlah Invoice | Total (Rp) |
|---|---|---|
| Total Tagihan | ... | Rp ... |
| Sudah Lunas | ... | Rp ... |
| Belum Bayar | ... | Rp ... |
| Terlambat | ... | Rp ... |
| Persentase Tercapai | — | ...% |

---

## 9. Memahami Dashboard

Halaman dashboard (`/admin`) menampilkan 2 widget:

### 9.1 Proyeksi Pemasukan

Tiga kartu di bagian atas:

| Kartu | Arti |
|---|---|
| **Proyeksi Pemasukan {tahun}** | Total yang seharusnya masuk berdasarkan semua kontrak aktif |
| **Sudah Masuk** | Invoice yang sudah berstatus Lunas di tahun ini, beserta % pencapaian |
| **Belum Masuk** | Invoice Belum Bayar + Terlambat di tahun ini |

> **Catatan:** Kontrak yang mulai di bulan Juli–Desember akan masuk ke proyeksi **tahun berikutnya**, bukan tahun ini.

### 9.2 Tagihan Mendekati Jatuh Tempo

Tabel di bawah kartu menampilkan invoice yang:
- Berstatus **Belum Bayar**
- Jatuh tempo dalam **1 bulan ke depan**

Gunakan tabel ini sebagai pengingat untuk menghubungi penyewa yang akan segera jatuh tempo.

---

## 10. Alur Kerja Lengkap

Berikut urutan langkah yang disarankan saat memulai menggunakan sistem:

```
SETUP AWAL
    │
    ▼
[1] Tambah data Properti
    (Properti → New Property)
    │
    ▼
[2] Tambah data Penyewa
    (Penyewa → New Penyewa)
    │
    ▼
[3] Buat Kontrak Sewa
    (Kontrak Sewa → New Kontrak)
    Isi: penyewa, properti, tanggal, harga, PPN, tagihan lainnya
    │
    ▼
[4] Generate Invoice
    php artisan invoices:generate
    (atau tunggu jadwal otomatis)
    │
    ▼
[5] Penyewa melakukan pembayaran
    │
    ├── [A] Bayar Langsung Penuh
    │       Invoice → Edit → Status: Lunas → Isi tanggal bayar → Save
    │
    └── [B] Bayar Bertahap (DP / Termin)
            Invoice → Edit → Riwayat Pembayaran → Tambah Pembayaran
            Isi: jenis (DP/Termin/Pelunasan), jumlah, tanggal, metode
            Ulangi untuk setiap transaksi
            Setelah lunas: ubah Status → Lunas
    │
    ▼
[6] Monitor via Dashboard
    Cek widget Proyeksi & Tagihan Mendekati Jatuh Tempo
    │
    ▼
[7] Export Laporan
    Invoice → Export 2026 / Export Semua
```

---

## Tips & Hal Penting

- **Backup database** secara rutin, terutama sebelum `migrate:fresh`
- Invoice **tidak bisa dihapus** dari UI, hanya statusnya yang bisa diubah
- Untuk **kontrak yang tidak diperpanjang**, cukup biarkan `tanggal_akhir` berlalu — sistem tidak akan generate invoice baru
- **Sisa Hari = null** berarti invoice sudah Lunas dan tidak perlu tindakan
- File Excel hasil export menggunakan format `.xlsx` — dapat dibuka dengan Microsoft Excel, Google Sheets, atau LibreOffice
