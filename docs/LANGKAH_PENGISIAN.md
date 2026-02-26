# Langkah-Langkah Pengisian — Property Management System

> Dokumen ini adalah panduan ringkas berisi langkah pengisian data dari awal hingga siap operasional.

---

## Daftar Isi

1. [Langkah 1 — Tambah Data Properti](#langkah-1--tambah-data-properti)
2. [Langkah 2 — Tambah Data Penyewa](#langkah-2--tambah-data-penyewa)
3. [Langkah 3 — Buat Kontrak Sewa](#langkah-3--buat-kontrak-sewa)
4. [Langkah 4 — Generate Invoice](#langkah-4--generate-invoice)
5. [Langkah 5 — Catat Pembayaran](#langkah-5--catat-pembayaran)
6. [Langkah 6 — Cek Dashboard](#langkah-6--cek-dashboard)
7. [Langkah 7 — Export Laporan](#langkah-7--export-laporan)

---

## Langkah 1 — Tambah Data Properti

**Menu:** Sidebar → **Properties** → tombol **New Property**

### Field yang diisi:

| No | Field | Wajib | Cara Mengisi | Contoh |
|----|-------|:-----:|--------------|--------|
| 1 | **Kode Lokasi** | Ya | Kode unik, tidak boleh duplikat | `BLG-A01` |
| 2 | **Nama** | Ya | Nama lengkap properti | `Gedung A Lantai 1` |
| 3 | **Status** | Ya | Pilih dari dropdown | `Tersedia` |
| 4 | **Catatan** | Tidak | Keterangan tambahan | `Luas 120m²` |

### Langkah:

1. Klik **New Property**
2. Isi **Kode Lokasi** — pastikan unik, tidak sama dengan properti lain
3. Isi **Nama** properti secara lengkap
4. Pilih **Status** = `Tersedia` untuk properti baru
5. Isi **Catatan** jika perlu (opsional)
6. Klik **Save**

> **Catatan:** Status properti akan otomatis berubah menjadi **Disewa** saat kontrak sewa dibuat.

---

## Langkah 2 — Tambah Data Penyewa

**Menu:** Sidebar → **Penyewa** → tombol **New Penyewa**

### Field yang diisi:

| No | Field | Wajib | Cara Mengisi | Contoh |
|----|-------|:-----:|--------------|--------|
| 1 | **Nama** | Ya | Nama lengkap atau nama perusahaan | `PT Maju Bersama` |
| 2 | **Kontak** | Ya | Nomor telepon aktif | `08123456789` |
| 3 | **Email** | Tidak | Format email yang valid | `kontak@majubersama.com` |
| 4 | **Alamat** | Tidak | Alamat lengkap penyewa | `Jl. Sudirman No. 10, Jakarta` |

### Langkah:

1. Klik **New Penyewa**
2. Isi **Nama** — nama lengkap individu atau badan usaha
3. Isi **Kontak** — nomor telepon yang bisa dihubungi
4. Isi **Email** jika ada (opsional)
5. Isi **Alamat** jika ada (opsional)
6. Klik **Save**

---

## Langkah 3 — Buat Kontrak Sewa

**Menu:** Sidebar → **Kontrak Sewa** → tombol **New Kontrak**

### Bagian A — Informasi Dasar:

| No | Field | Wajib | Cara Mengisi | Contoh |
|----|-------|:-----:|--------------|--------|
| 1 | **Penyewa** | Ya | Pilih dari dropdown, ketik untuk mencari | `PT Maju Bersama` |
| 2 | **Properti** | Ya | Pilih kode lokasi dari dropdown | `BLG-A01` |
| 3 | **Tanggal Mulai** | Ya | Klik kalender, pilih tanggal awal sewa | `01/01/2026` |
| 4 | **Tanggal Akhir** | Ya | Klik kalender, pilih tanggal akhir sewa | `31/12/2026` |
| 5 | **Periode Sewa** | Ya | Tuliskan keterangan periode | `Januari - Desember 2026` |
| 6 | **Harga Sewa / Bulan** | Ya | Angka saja, tanpa titik atau koma | `10000000` |
| 7 | **PPN (%)** | Ya | Default sudah 11%, ubah jika perlu | `11` |
| 8 | **PPB (%)** | Tidak | Persentase PPB jika ada | `0` |
| 9 | **Catatan** | Tidak | Keterangan tambahan kontrak | `Termasuk parkir 2 unit` |

### Bagian B — Tagihan Lainnya (opsional):

Biaya tambahan di luar harga sewa — misal: listrik, keamanan, IPL.

| No | Field | Cara Mengisi | Contoh |
|----|-------|--------------|--------|
| 1 | **Nama Tagihan** | Nama jenis biaya | `Listrik` |
| 2 | **Jumlah** | Nominal dalam Rupiah | `200000` |

### Langkah:

1. Klik **New Kontrak**
2. Pilih **Penyewa** dari dropdown
3. Pilih **Properti** dari dropdown
4. Isi **Tanggal Mulai** dan **Tanggal Akhir**
5. Isi **Periode Sewa** (contoh: `Januari - Desember 2026`)
6. Isi **Harga Sewa / Bulan** — angka saja tanpa pemisah ribuan
7. Periksa **PPN (%)** — default 11%, sesuaikan jika berbeda
8. *(Opsional)* Isi **PPB (%)** jika ada
9. *(Opsional)* Scroll ke bawah → klik **Add Tagihan Lainnya** untuk tambah biaya tambahan
10. Klik **Save**

### Rumus Perhitungan Invoice:

```
Total Invoice = Harga Sewa
              + (Harga Sewa × PPN%)
              + (Harga Sewa × PPB%)
              + Jumlah semua Tagihan Lainnya
```

**Contoh:**
- Harga Sewa: Rp 10.000.000
- PPN 11%: Rp 1.100.000
- Listrik: Rp 200.000
- **Total: Rp 11.300.000 / bulan**

---

## Langkah 4 — Generate Invoice

Invoice bulanan dibuat otomatis menggunakan perintah berikut di terminal server:

```bash
php artisan invoices:generate
```

### Yang dilakukan sistem:

1. Mencari semua kontrak **aktif** (tanggal mulai ≤ hari ini ≤ tanggal akhir)
2. Mengecek apakah invoice bulan ini sudah ada untuk tiap kontrak
3. Jika belum ada → buat invoice baru dengan status `Belum Bayar`
4. Jika sudah ada → dilewati (tidak duplikat)

### Jadwal otomatis (opsional):

Tambahkan ke crontab agar berjalan setiap tanggal 1 pukul 00.00:

```
0 0 1 * * cd /path/ke/proyek && php artisan invoices:generate
```

---

## Langkah 5 — Catat Pembayaran

**Menu:** Sidebar → **Invoice** → klik ikon **Edit** (pensil) pada baris invoice

### Skenario A — Bayar Langsung Penuh

1. Buka halaman edit invoice
2. Ubah **Status Pembayaran** → `Lunas`
3. Isi **Tanggal Bayar** (field muncul otomatis saat status = Lunas)
4. *(Opsional)* Isi **Catatan Pembayaran** — misal: nomor referensi transfer
5. Klik **Save**

---

### Skenario B — Bayar Bertahap (DP / Termin)

**Langkah A — Catat setiap transaksi masuk:**

1. Buka halaman edit invoice
2. Scroll ke bawah → temukan tabel **Riwayat Pembayaran**
3. Klik **Tambah Pembayaran**
4. Isi form:

| No | Field | Wajib | Cara Mengisi | Contoh |
|----|-------|:-----:|--------------|--------|
| 1 | **Jenis Pembayaran** | Ya | Pilih: DP / Termin / Pelunasan | `DP` |
| 2 | **Jumlah Dibayar** | Ya | Nominal yang diterima kali ini | `5000000` |
| 3 | **Tanggal Bayar** | Ya | Tanggal uang diterima | `01/02/2026` |
| 4 | **Metode Pembayaran** | Tidak | Transfer Bank / Tunai / Cek/Giro / QRIS | `Transfer Bank` |
| 5 | **No. Referensi** | Tidak | Kode transaksi dari bank | `TRF-0226-001` |
| 6 | **Catatan** | Tidak | Keterangan tambahan | — |

5. Klik **Save**
6. Ulangi untuk setiap transaksi berikutnya

**Langkah B — Setelah lunas sepenuhnya:**

1. Ubah **Status Pembayaran** → `Lunas`
2. Isi **Tanggal Bayar**
3. Klik **Save**

### Jenis Pembayaran:

| Jenis | Kapan Digunakan |
|-------|-----------------|
| `DP` | Uang muka di awal — pembayaran pertama sebelum lunas |
| `Termin` | Cicilan per tahap di tengah periode |
| `Pelunasan` | Pembayaran terakhir yang menutup sisa tagihan |

### Membaca informasi di halaman invoice:

| Field | Arti |
|-------|------|
| **Total Terbayar** | Akumulasi semua riwayat pembayaran yang sudah dicatat |
| **Sisa Tagihan** | Jumlah Tagihan dikurangi Total Terbayar |

---

## Langkah 6 — Cek Dashboard

**Menu:** Sidebar → **Dashboard**

### Widget 1 — Proyeksi Pemasukan

Tiga kartu ringkasan tahunan:

| Kartu | Isi |
|-------|-----|
| **Proyeksi Pemasukan {tahun}** | Total yang seharusnya masuk dari semua kontrak aktif |
| **Sudah Masuk** | Total invoice berstatus Lunas + persentase pencapaian |
| **Belum Masuk** | Total invoice berstatus Belum Bayar dan Terlambat |

> Kontrak yang mulai di bulan **Juli–Desember** masuk ke proyeksi **tahun berikutnya**.

### Widget 2 — Tagihan Mendekati Jatuh Tempo

Tabel invoice yang:
- Status **Belum Bayar**
- Jatuh tempo dalam **1 bulan ke depan**

Gunakan tabel ini untuk menghubungi penyewa sebelum jatuh tempo.

### Indikator Warna Sisa Hari:

| Warna | Kondisi |
|-------|---------|
| Abu-abu | Lebih dari 3 hari, atau sudah Lunas (tampil `-`) |
| Kuning | Tinggal 1–3 hari lagi |
| Merah | Sudah melewati jatuh tempo |

---

## Langkah 7 — Export Laporan

**Menu:** Sidebar → **Invoice**

### Export Semua / Tahun Ini:

1. Buka halaman daftar invoice
2. Klik salah satu tombol di pojok kanan atas:
   - **Export Semua (.xlsx)** — seluruh data invoice
   - **Export 2026 (.xlsx)** — invoice tahun berjalan saja
3. File `.xlsx` otomatis terunduh

### Export Invoice Terpilih:

1. Centang checkbox di baris kiri tabel untuk memilih invoice
2. Klik dropdown **Actions** (muncul setelah ada yang dipilih)
3. Pilih **Export Terpilih (.xlsx)**
4. File otomatis terunduh

### Isi File Excel:

**Sheet 1 — Data Invoice:**

| Kolom | Keterangan |
|-------|------------|
| No | Nomor urut |
| Penyewa | Nama penyewa |
| Lokasi | Kode properti |
| Bulan/Tahun | Periode tagihan |
| Jatuh Tempo | Batas waktu pembayaran |
| Jumlah Tagihan | Total tagihan |
| Status | Lunas / Belum Bayar / Terlambat |
| Tanggal Bayar | Tanggal pelunasan (jika Lunas) |
| Catatan | Catatan pembayaran |

**Sheet 2 — Ringkasan:**

| Baris | Isi |
|-------|-----|
| Total Tagihan | Jumlah seluruh invoice |
| Sudah Lunas | Invoice berstatus Lunas |
| Belum Bayar | Invoice berstatus Belum Bayar |
| Terlambat | Invoice berstatus Terlambat |
| Persentase Tercapai | Lunas ÷ Total × 100% |

---

## Ringkasan Alur Pengisian

```
[1] Tambah Properti
     Sidebar > Properties > New Property
     Isi: kode lokasi, nama, status (Tersedia)
       |
       v
[2] Tambah Penyewa
     Sidebar > Penyewa > New Penyewa
     Isi: nama, kontak, email, alamat
       |
       v
[3] Buat Kontrak Sewa
     Sidebar > Kontrak Sewa > New Kontrak
     Isi: penyewa, properti, tanggal, harga sewa, PPN, tagihan lainnya
       |
       v
[4] Generate Invoice
     Terminal: php artisan invoices:generate
     (atau jadwal otomatis tiap tgl 1)
       |
       v
[5] Catat Pembayaran
     Sidebar > Invoice > Edit
       |
       +-- Bayar Penuh:
       |   Ubah status ke Lunas, isi tanggal bayar
       |
       +-- Bayar Bertahap:
           Riwayat Pembayaran > Tambah Pembayaran
           Catat tiap transaksi (DP / Termin / Pelunasan)
           Setelah lunas: ubah status ke Lunas
               |
               v
[6] Monitor Dashboard
     Cek proyeksi pemasukan & tagihan mendekati jatuh tempo
               |
               v
[7] Export Laporan
     Invoice > Export Semua / Export 2026 / Export Terpilih
```

---

## Catatan Penting

| # | Hal | Keterangan |
|---|-----|------------|
| 1 | Status properti | Berubah otomatis ke **Disewa** saat kontrak dibuat |
| 2 | Invoice | Dibuat otomatis oleh sistem, tidak perlu input manual |
| 3 | Hapus invoice | Tidak bisa dihapus dari UI, hanya statusnya yang bisa diubah |
| 4 | Kontrak habis | Biarkan `tanggal_akhir` berlalu — invoice baru tidak akan dibuat |
| 5 | Backup | Lakukan backup database secara rutin |
| 6 | Kontrak Jul–Des | Proyeksi masuk ke tahun berikutnya, bukan tahun berjalan |
