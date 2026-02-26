# Dokumentasi Teknis — Property Management System

> Laravel 12 + Filament 3.3
> Versi dokumen: 1.0 — Februari 2026

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Arsitektur & Tech Stack](#2-arsitektur--tech-stack)
3. [Struktur Direktori](#3-struktur-direktori)
4. [Database & Migrasi](#4-database--migrasi)
5. [Models & Relasi](#5-models--relasi)
6. [Filament Admin Panel](#6-filament-admin-panel)
7. [Widget Dashboard](#7-widget-dashboard)
8. [Export Excel](#8-export-excel)
9. [Artisan Command](#9-artisan-command)
10. [Test Suite](#10-test-suite)
11. [Seeder & Data Demo](#11-seeder--data-demo)
12. [Aturan Bisnis](#12-aturan-bisnis)
13. [Cara Menjalankan Proyek](#13-cara-menjalankan-proyek)

---

## 1. Gambaran Umum

Property Management System adalah aplikasi berbasis web untuk mengelola properti sewaan. Sistem ini membantu pengelola properti dalam mencatat data penyewa, membuat kontrak sewa, menerbitkan invoice bulanan, melacak status pembayaran, mencatat riwayat pembayaran termin/DP, serta memproyeksikan pemasukan per tahun fiskal.

### Fitur Utama

| Fitur | Keterangan |
|---|---|
| Manajemen Properti | CRUD properti dengan status Tersedia / Disewa / Maintenance |
| Manajemen Penyewa | CRUD data penyewa (perorangan & perusahaan) |
| Kontrak Sewa | Buat kontrak dengan harga, PPN, PPB, dan tagihan lainnya |
| Invoice Bulanan | Generate otomatis via command `invoices:generate` |
| Status Pembayaran | Belum Bayar / Lunas / Terlambat, dengan tanggal bayar |
| Riwayat Pembayaran | Catat DP, Termin, Pelunasan per invoice |
| Export Excel | Export invoice ke `.xlsx` dengan 2 sheet (detail + ringkasan) |
| Dashboard Widget | Proyeksi pemasukan & tagihan mendekati jatuh tempo |

---

## 2. Arsitektur & Tech Stack

```
┌─────────────────────────────────────────────┐
│              Browser / Admin                │
└────────────────────┬────────────────────────┘
                     │ HTTPS
┌────────────────────▼────────────────────────┐
│         Filament 3.3 Admin Panel            │
│   Resources │ Pages │ Widgets │ Actions     │
└────────────────────┬────────────────────────┘
                     │
┌────────────────────▼────────────────────────┐
│           Laravel 12 Application            │
│   Models │ Commands │ Exports │ Migrations  │
└────────────────────┬────────────────────────┘
                     │
┌────────────────────▼────────────────────────┐
│              Database (SQLite)              │
│  properties │ tenants │ leases │ invoices   │
│             payment_histories               │
└─────────────────────────────────────────────┘
```

| Komponen | Versi |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Filament | 3.3 |
| maatwebsite/excel | ^3.1 |
| phpoffice/phpspreadsheet | ^1.30 |
| PHPUnit | ^11.5 |
| Database | SQLite (dev), MySQL compatible |

---

## 3. Struktur Direktori

```
app/
├── Console/Commands/
│   └── GenerateMonthlyInvoices.php   ← Artisan command generate invoice bulanan
├── Exports/
│   ├── InvoicesExport.php            ← Sheet detail invoice (Excel)
│   ├── InvoicesSummaryExport.php     ← Entry point multi-sheet export
│   └── InvoicesSummarySheetExport.php ← Sheet ringkasan (Excel)
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php             ← Halaman dashboard utama
│   ├── Resources/
│   │   ├── PropertyResource.php
│   │   ├── TenantResource.php
│   │   ├── LeaseResource.php
│   │   └── InvoiceResource.php
│   │       └── RelationManagers/
│   │           └── PaymentHistoriesRelationManager.php
│   └── Widgets/
│       ├── RevenueProjectionWidget.php
│       └── UpcomingInvoices.php
└── Models/
    ├── Property.php
    ├── Tenant.php
    ├── Lease.php
    ├── Invoice.php
    ├── PaymentHistory.php
    └── User.php

database/
├── migrations/
│   ├── ..._create_properties_table.php
│   ├── ..._create_tenants_table.php
│   ├── ..._create_leases_table.php
│   ├── ..._create_invoices_table.php
│   └── ..._create_payment_histories_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── PropertyManagementSeeder.php

tests/
├── Unit/
│   ├── InvoiceModelTest.php
│   ├── LeaseModelTest.php
│   └── RevenueProjectionYearTest.php
└── Feature/
    ├── GenerateMonthlyInvoicesTest.php
    ├── InvoiceResourceTest.php
    ├── TenantResourceTest.php
    └── RevenueProjectionWidgetTest.php
```

---

## 4. Database & Migrasi

### ERD Ringkasan

```
properties          tenants
    │                  │
    └──── leases ───────┘
              │
           invoices
              │
      payment_histories
```

### Tabel `properties`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode_lokasi | string UNIQUE | Kode unik lokasi, misal `BLG-A01` |
| nama | string | Nama properti |
| status | enum | `Tersedia` / `Disewa` / `Maintenance` |
| catatan | text nullable | |
| timestamps | | |

### Tabel `tenants`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nama | string | Nama penyewa / perusahaan |
| kontak | string nullable | No. telepon |
| email | string nullable | |
| alamat | text nullable | |
| timestamps | | |

### Tabel `leases`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| property_id | FK → properties | |
| tanggal_mulai | date | |
| tanggal_akhir | date | |
| periode | string | Deskripsi, misal `1 tahun` |
| harga_sewa | decimal(15,2) | Harga sewa per bulan |
| ppn_persen | decimal(5,2) | Default 11.00 |
| ppb_persen | decimal(5,2) | Default 0.00 |
| tagihan_lainnya | json nullable | Array `[{nama, jumlah}]` |
| catatan | text nullable | |
| timestamps | | |

### Tabel `invoices`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| lease_id | FK → leases | |
| bulan_tahun | string | Misal `Februari 2026` |
| tanggal_jatuh_tempo | date | |
| jumlah_tagihan | decimal(15,2) | Total termasuk PPN + tagihan lain |
| status_pembayaran | enum | `Belum Bayar` / `Lunas` / `Terlambat` |
| tanggal_bayar | date nullable | Diisi saat status = Lunas |
| catatan_pembayaran | text nullable | |
| timestamps | | |

### Tabel `payment_histories`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| invoice_id | FK → invoices | |
| jenis | enum | `DP` / `Termin` / `Pelunasan` |
| jumlah_bayar | decimal(15,2) | Nominal per transaksi |
| tanggal_bayar | date | |
| metode_bayar | string nullable | Transfer Bank / Tunai / Cek/Giro / QRIS |
| referensi | string nullable | No. kode transaksi |
| catatan | text nullable | |
| timestamps | | |

---

## 5. Models & Relasi

### Diagram Relasi

```
Property ──hasMany──► Lease ──hasMany──► Invoice ──hasMany──► PaymentHistory
Tenant   ──hasMany──► Lease
```

### `Property`

```php
fillable: kode_lokasi, nama, status, catatan
hasMany:  leases()
hasOne:   currentLease()  // kontrak yang sedang aktif
```

### `Tenant`

```php
fillable: nama, kontak, email, alamat
hasMany:  leases()
```

### `Lease`

```php
fillable: tenant_id, property_id, tanggal_mulai, tanggal_akhir,
          periode, harga_sewa, ppn_persen, ppb_persen,
          tagihan_lainnya, catatan
casts:    tagihan_lainnya → array
          tanggal_mulai   → date
          tanggal_akhir   → date
belongsTo: tenant(), property()
hasMany:   invoices()
```

### `Invoice`

```php
fillable: lease_id, bulan_tahun, tanggal_jatuh_tempo, jumlah_tagihan,
          status_pembayaran, tanggal_bayar, catatan_pembayaran
casts:    tanggal_jatuh_tempo → date
          tanggal_bayar       → date
          jumlah_tagihan      → decimal:2
belongsTo: lease()
hasMany:   paymentHistories()

// Computed attributes
getTotalTerbayarAttribute(): float   // sum jumlah_bayar dari riwayat
getSisaTagihanAttribute():  float   // jumlah_tagihan - total_terbayar
getSisaHariAttribute():     ?int    // null jika Lunas, int jika tidak
```

### `PaymentHistory`

```php
fillable: invoice_id, jenis, jumlah_bayar, tanggal_bayar,
          metode_bayar, referensi, catatan
casts:    tanggal_bayar → date
          jumlah_bayar  → decimal:2
belongsTo: invoice()
```

---

## 6. Filament Admin Panel

### URL Admin: `/admin`

### Resource List

| Resource | URL | Fitur |
|---|---|---|
| PropertyResource | `/admin/properties` | CRUD, badge status |
| TenantResource | `/admin/tenants` | CRUD, search, hitung kontrak |
| LeaseResource | `/admin/leases` | CRUD, Repeater tagihan lainnya |
| InvoiceResource | `/admin/invoices` | Edit status, export xlsx, riwayat pembayaran |

---

### PropertyResource

**Form fields:**

| Field | Tipe | Rules |
|---|---|---|
| kode_lokasi | TextInput | required, unique |
| nama | TextInput | required |
| status | Select | Tersedia / Disewa / Maintenance |
| catatan | Textarea | optional |

**Table columns:** kode_lokasi, nama, status (badge warna)

---

### TenantResource

**Form fields:**

| Field | Tipe | Rules |
|---|---|---|
| nama | TextInput | required |
| kontak | TextInput | required |
| email | TextInput email | optional |
| alamat | Textarea | optional |

**Table columns:** nama (searchable), kontak, email (searchable), alamat, leases_count

**Actions:** Edit, Delete (dengan konfirmasi)

---

### LeaseResource

**Form fields (2 kolom):**

| Field | Tipe | Rules |
|---|---|---|
| tenant_id | Select + relationship | required, searchable |
| property_id | Select + relationship | required, live — auto set status Disewa |
| tanggal_mulai | DatePicker | required |
| tanggal_akhir | DatePicker | required |
| periode | TextInput | required |
| harga_sewa | TextInput numeric | required, prefix Rp |
| ppn_persen | TextInput numeric | required, default 11% |
| ppb_persen | TextInput numeric | default 0% |
| tagihan_lainnya | Repeater (nama+jumlah) | optional, JSON array |
| catatan | Textarea | optional, full width |

**Catatan:** Saat properti dipilih, status properti otomatis diubah ke `Disewa`.

---

### InvoiceResource

**Form (read-only info + editable):**

| Field | Tipe | Editable |
|---|---|---|
| Penyewa | Placeholder | ✗ |
| Lokasi | Placeholder | ✗ |
| Bulan/Tahun | Placeholder | ✗ |
| Jumlah Tagihan | Placeholder | ✗ |
| Jatuh Tempo | Placeholder | ✗ |
| Total Terbayar | Placeholder (computed) | ✗ |
| Sisa Tagihan | Placeholder (computed) | ✗ |
| status_pembayaran | Select | ✓ required, live |
| tanggal_bayar | DatePicker | ✓ (visible jika status = Lunas) |
| catatan_pembayaran | Textarea | ✓ |

**Table columns:** Penyewa, Lokasi, Bulan/Tahun, Jatuh Tempo, Tagihan, Status (badge), Sisa Hari

**Filter:**
- Filter by status pembayaran
- Filter `due_soon` — jatuh tempo dalam 1 bulan, status Belum Bayar

**Bulk Actions:** Export terpilih → `.xlsx`

**Header Actions (di halaman List):**
- Export Semua
- Export tahun berjalan (misal Export 2026)

**Relation Manager:** Riwayat Pembayaran (lihat §6.5)

---

### PaymentHistoriesRelationManager

Tersedia di halaman edit invoice. Memungkinkan pencatatan pembayaran bertahap.

**Form fields:**

| Field | Tipe | Rules |
|---|---|---|
| jenis | Select | DP / Termin / Pelunasan; required |
| jumlah_bayar | TextInput numeric | required, min 1 |
| tanggal_bayar | DatePicker | required, default hari ini |
| metode_bayar | Select | Transfer Bank / Tunai / Cek/Giro / QRIS |
| referensi | TextInput | optional |
| catatan | Textarea | optional, full width |

**Table columns:** Tanggal, Jenis (badge), Jumlah Dibayar, Metode, Referensi, Catatan

---

## 7. Widget Dashboard

### RevenueProjectionWidget (StatsOverview)

Menampilkan 3 kartu di atas dashboard:

| Kartu | Nilai | Keterangan |
|---|---|---|
| Proyeksi Pemasukan {tahun} | Rp xxx | Total dari semua kontrak yang masuk proyeksi tahun ini |
| Sudah Masuk | Rp xxx | Sum invoice Lunas di tahun ini |
| Belum Masuk | Rp xxx | Sum invoice Belum Bayar + Terlambat di tahun ini |

**Aturan proyeksi periode:**

| Bulan mulai kontrak | Proyeksi masuk ke |
|---|---|
| Januari – Juni | Tahun yang sama |
| Juli – Desember | Tahun berikutnya |

**Cara hitung proyeksi:**
```
Untuk tiap kontrak yang memenuhi aturan tahun proyeksi:
  overlap = irisan(periode_kontrak, tahun_fiskal)
  jumlah_bulan = hitung bulan kalender dalam overlap
  nilai_per_bulan = harga_sewa + PPN + PPB + sum(tagihan_lainnya)
  proyeksi += nilai_per_bulan × jumlah_bulan
```

---

### UpcomingInvoices (TableWidget)

Menampilkan invoice dengan kondisi:
- `status_pembayaran = 'Belum Bayar'`
- `tanggal_jatuh_tempo` antara sekarang dan 1 bulan ke depan

Kolom: Penyewa, Lokasi, Jatuh Tempo, Total Tagihan, Sisa Hari (warna merah jika ≤0, kuning jika ≤3)

---

## 8. Export Excel

### File yang Terlibat

```
app/Exports/
├── InvoicesSummaryExport.php       ← WithMultipleSheets (entry point)
├── InvoicesExport.php              ← Sheet "Data Invoice"
└── InvoicesSummarySheetExport.php  ← Sheet "Ringkasan"
```

### Sheet 1 — Data Invoice

| Kolom | Keterangan |
|---|---|
| No. | Nomor urut |
| Penyewa | Dari relasi lease.tenant.nama |
| Lokasi | Dari relasi lease.property.kode_lokasi |
| Bulan/Tahun | |
| Jatuh Tempo | Format d/m/Y |
| Jumlah Tagihan (Rp) | Angka |
| Status | Belum Bayar / Lunas / Terlambat |
| Tanggal Bayar | d/m/Y atau `-` |
| Catatan | |

Header: bold putih, background biru tua `#1E3A5F`, auto-size kolom.

### Sheet 2 — Ringkasan

Menampilkan:
- Total Tagihan (count + sum)
- Sudah Lunas (count + sum)
- Belum Bayar (count + sum)
- Terlambat (count + sum)
- Persentase Tercapai (%)

### Mode Export

| Tombol | Class | Filter |
|---|---|---|
| Export Semua | `InvoicesSummaryExport()` | Semua invoice |
| Export 2026 | `InvoicesSummaryExport(year: 2026)` | Filter tahun |
| Export Terpilih | `InvoicesSummaryExport(ids: [...])` | Filter ID terpilih |

---

## 9. Artisan Command

### `php artisan invoices:generate`

**Deskripsi:** Generate invoice bulanan untuk semua kontrak yang sedang aktif.

**Alur:**

```
1. Ambil semua Lease dengan tanggal_mulai ≤ hari ini ≤ tanggal_akhir
2. Untuk tiap lease:
   a. Hitung bulan ke-N dari tanggal_mulai ke hari ini
   b. Hitung tanggal jatuh tempo = startOfMonth + N bulan → endOfMonth
   c. Format bulan_tahun = "Februari 2026"
   d. Cek apakah invoice untuk bulan ini sudah ada → skip jika ada
   e. Hitung total = harga_sewa + PPN + PPB + sum(tagihan_lainnya)
   f. Buat Invoice dengan status "Belum Bayar"
3. Tampilkan hasil: N invoice baru dibuat
```

**Formula total tagihan:**

```
harga    = harga_sewa
ppn      = harga × (ppn_persen / 100)
ppb      = harga × (ppb_persen / 100)
lain     = sum(tagihan_lainnya[].jumlah)
total    = harga + ppn + ppb + lain
```

**Idempotent:** Aman dijalankan berulang kali, tidak membuat duplikat.

---

## 10. Test Suite

### Menjalankan Test

```bash
php artisan test
# atau spesifik suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Ringkasan Test

| File | Jumlah Test | Coverage |
|---|---|---|
| `Unit/InvoiceModelTest` | 4 | sisa_hari null/int/negatif |
| `Unit/LeaseModelTest` | 5 | JSON cast, date cast, relasi |
| `Unit/RevenueProjectionYearTest` | 4 | Aturan geser periode |
| `Feature/GenerateMonthlyInvoicesTest` | 7 | Command generate, duplikat, kalkulasi |
| `Feature/InvoiceResourceTest` | 7 | CRUD, validasi, sisa_hari |
| `Feature/TenantResourceTest` | 6 | CRUD, search, delete |
| `Feature/RevenueProjectionWidgetTest` | 5 | Proyeksi, filter tahun |
| **Total** | **38** | |

**Konfigurasi test:** SQLite in-memory (`phpunit.xml`), `RefreshDatabase` per test.

---

## 11. Seeder & Data Demo

### Menjalankan Seeder

```bash
php artisan migrate:fresh --seed
```

### Data yang Dibuat

**5 Properti:**

| Kode | Nama | Status |
|---|---|---|
| BLG-A01 | Gedung A Lantai 1 | Disewa |
| BLG-A02 | Gedung A Lantai 2 | Disewa |
| KIOS-01 | Kios Pasar Pagi No.1 | Disewa |
| RUKO-05 | Ruko Jl. Merdeka No.5 | Disewa |
| BLG-B01 | Gedung B Lantai 1 | Tersedia |

**5 Penyewa & 5 Kontrak:**

| Penyewa | Properti | Periode | Skenario |
|---|---|---|---|
| PT Maju Bersama | BLG-A01 | Des 2025–Des 2026 | Aktif, ada tagihan listrik + keamanan |
| CV Sejahtera Abadi | BLG-A02 | Jan 2026–Jan 2027 | Aktif, tanpa tagihan lain |
| Budi Santoso | KIOS-01 | Feb 2026–Jul 2026 | Baru mulai, ada IPL |
| Toko Elektronik Rina | RUKO-05 | Agt 2025–Feb 2026 | Hampir berakhir, banyak tunggakan |
| PT Nusantara Logistik | BLG-B01 | Jan 2025–Jan 2026 | Expired |

**26 Invoice** dengan berbagai status (Lunas, Belum Bayar, Terlambat).

### Akun Admin Demo

| Field | Value |
|---|---|
| Email | `admin@property.test` |
| Password | `password` |

---

## 12. Aturan Bisnis

### Status Pembayaran Invoice

| Status | Warna | Keterangan |
|---|---|---|
| Belum Bayar | Merah | Invoice baru atau belum dilunasi |
| Lunas | Hijau | Sudah dibayar penuh, `sisa_hari` = null |
| Terlambat | Kuning | Melewati jatuh tempo, belum bayar |

### Sisa Hari

```php
// null  → jika status = Lunas
// int   → today().diffInDays(tanggal_jatuh_tempo) jika tidak Lunas
// negatif → sudah melewati jatuh tempo
```

Tampilan warna di tabel:
- `≤ 0` → merah
- `≤ 3` → kuning
- `> 3` atau null → abu-abu

### Total Terbayar & Sisa Tagihan

```
total_terbayar = sum(payment_histories.jumlah_bayar) untuk invoice ini
sisa_tagihan   = max(0, jumlah_tagihan - total_terbayar)
```

### Proyeksi Fiskal

- Kontrak mulai **Jan–Jun** → proyeksi masuk tahun yang sama
- Kontrak mulai **Jul–Des** → proyeksi digeser ke tahun berikutnya
- Kontrak lintas tahun dihitung berdasarkan bulan overlap dengan tahun fiskal

### Auto-update Status Properti

Ketika kontrak baru dibuat dan properti dipilih, status properti otomatis berubah ke `Disewa` (via `afterStateUpdated` di LeaseResource).

---

## 13. Cara Menjalankan Proyek

### Instalasi

```bash
# 1. Clone & install dependencies
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Buat file database SQLite
touch database/database.sqlite

# 4. Migrasi & seeder
php artisan migrate --seed

# 5. Jalankan server
php artisan serve
```

### Akses Admin

Buka `http://localhost:8000/admin` lalu login dengan akun demo.

### Generate Invoice Bulanan

```bash
php artisan invoices:generate
```

Tambahkan ke cron untuk otomatis (contoh: setiap tanggal 1 pukul 00:00):

```
0 0 1 * * php /path/to/project/artisan invoices:generate
```

### Menjalankan Test

```bash
php artisan test
```
