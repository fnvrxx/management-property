<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Skenario Seeder - Property Management System
 * Tanggal referensi: Februari 2026
 *
 * PROPERTI (5):
 *   P1 BLG-A01  Gedung A Lt.1     → Disewa (Skenario 1)
 *   P2 BLG-A02  Gedung A Lt.2     → Disewa (Skenario 2)
 *   P3 KIOS-01  Kios Pasar No.1   → Disewa (Skenario 3)
 *   P4 RUKO-05  Ruko Jl. Merdeka  → Disewa (Skenario 4 - hampir berakhir)
 *   P5 BLG-B01  Gedung B Lt.1     → Tersedia (tidak disewa / penyewa lama sudah keluar)
 *
 * PENYEWA (5):
 *   T1 PT Maju Bersama        → aktif di P1
 *   T2 CV Sejahtera Abadi     → aktif di P2
 *   T3 Budi Santoso           → aktif di P3
 *   T4 Toko Elektronik Rina   → aktif di P4 (kontrak hampir habis)
 *   T5 PT Nusantara Logistik  → tidak aktif (kontrak sudah expired di P5)
 *
 * KONTRAK (5):
 *   L1 T1-P1  Des 2025 – Des 2026  Aktif, ada tagihan lainnya (Listrik + Keamanan)
 *   L2 T2-P2  Jan 2026 – Jan 2027  Aktif, tanpa tagihan lainnya
 *   L3 T3-P3  Feb 2026 – Jul 2026  Baru mulai bulan ini, ada tagihan lainnya (IPL)
 *   L4 T4-P4  Agt 2025 – Feb 2026  Hampir berakhir (jatuh tempo bulan ini)
 *   L5 T5-P5  Jan 2025 – Jan 2026  Sudah expired
 *
 * INVOICE (11):
 *   L1: Des-25 (Lunas), Jan-26 (Lunas), Feb-26 (Belum Bayar ← jatuh tempo 5 hari lagi)
 *   L2: Jan-26 (Lunas), Feb-26 (Belum Bayar ← jatuh tempo 3 hari lagi)
 *   L3: Feb-26 (Belum Bayar ← baru dibuat)
 *   L4: Agt-25 (Lunas), Sep-25 (Lunas), Okt-25 (Lunas), Nov-25 (Terlambat), Des-25 (Terlambat), Jan-26 (Terlambat), Feb-26 (Belum Bayar)
 *   L5: Jan-25 (Lunas), ..., Jan-26 (Terlambat)
 */
class PropertyManagementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('invoices')->delete();
        DB::table('leases')->delete();
        DB::table('properties')->delete();
        DB::table('tenants')->delete();

        $this->command->info('🗑️  Tabel dikosongkan.');

        // =====================================================================
        // 1. PROPERTI
        // =====================================================================
        $p1 = Property::create(['kode_lokasi' => 'BLG-A01', 'nama' => 'Gedung A Lantai 1',    'status' => 'Disewa',    'catatan' => 'Ruang kantor 120m²']);
        $p2 = Property::create(['kode_lokasi' => 'BLG-A02', 'nama' => 'Gedung A Lantai 2',    'status' => 'Disewa',    'catatan' => 'Ruang kantor 120m²']);
        $p3 = Property::create(['kode_lokasi' => 'KIOS-01', 'nama' => 'Kios Pasar Pagi No.1', 'status' => 'Disewa',    'catatan' => 'Kios ukuran 3x4m']);
        $p4 = Property::create(['kode_lokasi' => 'RUKO-05', 'nama' => 'Ruko Jl. Merdeka No.5','status' => 'Disewa',    'catatan' => '2 lantai, luas 60m²']);
        $p5 = Property::create(['kode_lokasi' => 'BLG-B01', 'nama' => 'Gedung B Lantai 1',    'status' => 'Tersedia',  'catatan' => 'Penyewa lama sudah keluar Jan 2026']);

        $this->command->info('🏢  5 properti dibuat.');

        // =====================================================================
        // 2. PENYEWA
        // =====================================================================
        $t1 = Tenant::create(['nama' => 'PT Maju Bersama',       'kontak' => '021-55501234', 'email' => 'admin@majubersama.co.id',   'alamat' => 'Jl. Sudirman No.12, Jakarta']);
        $t2 = Tenant::create(['nama' => 'CV Sejahtera Abadi',    'kontak' => '021-55505678', 'email' => 'info@sejahtera.com',         'alamat' => 'Jl. Gatot Subroto Kav.8, Jakarta']);
        $t3 = Tenant::create(['nama' => 'Budi Santoso',          'kontak' => '08123456789',  'email' => 'budi.santoso@gmail.com',     'alamat' => 'Jl. Anggrek No.3, Bekasi']);
        $t4 = Tenant::create(['nama' => 'Toko Elektronik Rina',  'kontak' => '08234567890',  'email' => 'rina.elektronik@yahoo.com',  'alamat' => 'Jl. Pahlawan No.7, Depok']);
        $t5 = Tenant::create(['nama' => 'PT Nusantara Logistik', 'kontak' => '021-44409876', 'email' => 'ops@nusantara-logistik.id',  'alamat' => 'Jl. Industri Raya No.45, Cikarang']);

        $this->command->info('👤  5 penyewa dibuat.');

        // =====================================================================
        // 3. KONTRAK SEWA
        // =====================================================================

        // --- L1: PT Maju Bersama | BLG-A01 | Des 2025 – Des 2026 ---
        // Kontrak aktif, ada tagihan listrik & keamanan
        $l1 = Lease::create([
            'tenant_id'       => $t1->id,
            'property_id'     => $p1->id,
            'tanggal_mulai'   => Carbon::create(2025, 12, 1),
            'tanggal_akhir'   => Carbon::create(2026, 12, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 12000000,
            'ppn_persen'      => 11.00,
            'ppb_persen'      => 0.00,
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 200000],
                ['nama' => 'Keamanan', 'jumlah' => 75000],
            ],
            'catatan' => 'Kontrak perpanjangan dari tahun sebelumnya.',
        ]);

        // --- L2: CV Sejahtera Abadi | BLG-A02 | Jan 2026 – Jan 2027 ---
        // Kontrak aktif, tanpa tagihan lainnya
        $l2 = Lease::create([
            'tenant_id'       => $t2->id,
            'property_id'     => $p2->id,
            'tanggal_mulai'   => Carbon::create(2026, 1, 1),
            'tanggal_akhir'   => Carbon::create(2027, 1, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 9500000,
            'ppn_persen'      => 11.00,
            'ppb_persen'      => 0.00,
            'tagihan_lainnya' => null,
            'catatan'         => null,
        ]);

        // --- L3: Budi Santoso | KIOS-01 | Feb 2026 – Jul 2026 ---
        // Baru mulai bulan ini, kontrak 6 bulan, ada tagihan IPL
        $l3 = Lease::create([
            'tenant_id'       => $t3->id,
            'property_id'     => $p3->id,
            'tanggal_mulai'   => Carbon::create(2026, 2, 1),
            'tanggal_akhir'   => Carbon::create(2026, 7, 31),
            'periode'         => '6 bulan',
            'harga_sewa'      => 2500000,
            'ppn_persen'      => 11.00,
            'ppb_persen'      => 0.00,
            'tagihan_lainnya' => [
                ['nama' => 'IPL (Iuran Pengelolaan)', 'jumlah' => 100000],
            ],
            'catatan' => 'Penyewa baru, cicilan pertama dibayar di muka.',
        ]);

        // --- L4: Toko Elektronik Rina | RUKO-05 | Agt 2025 – Feb 2026 ---
        // Kontrak hampir berakhir akhir bulan ini, banyak tunggakan
        $l4 = Lease::create([
            'tenant_id'       => $t4->id,
            'property_id'     => $p4->id,
            'tanggal_mulai'   => Carbon::create(2025, 8, 1),
            'tanggal_akhir'   => Carbon::create(2026, 2, 28),
            'periode'         => '7 bulan',
            'harga_sewa'      => 5000000,
            'ppn_persen'      => 11.00,
            'ppb_persen'      => 0.00,
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 120000],
            ],
            'catatan' => 'Perlu dikonfirmasi apakah akan perpanjang kontrak.',
        ]);

        // --- L5: PT Nusantara Logistik | BLG-B01 | Jan 2025 – Jan 2026 ---
        // Kontrak sudah expired
        $l5 = Lease::create([
            'tenant_id'       => $t5->id,
            'property_id'     => $p5->id,
            'tanggal_mulai'   => Carbon::create(2025, 1, 1),
            'tanggal_akhir'   => Carbon::create(2026, 1, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 15000000,
            'ppn_persen'      => 11.00,
            'ppb_persen'      => 0.00,
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 350000],
                ['nama' => 'Keamanan', 'jumlah' => 100000],
                ['nama' => 'Kebersihan', 'jumlah' => 50000],
            ],
            'catatan' => 'Kontrak tidak diperpanjang. Properti kosong per Feb 2026.',
        ]);

        $this->command->info('📄  5 kontrak sewa dibuat.');

        // =====================================================================
        // 4. INVOICE
        // =====================================================================

        // Helper: hitung total dari lease
        $calcTotal = function (Lease $lease): float {
            $harga = $lease->harga_sewa;
            $ppn   = $harga * ($lease->ppn_persen / 100);
            $ppb   = $harga * ($lease->ppb_persen / 100);
            $lain  = collect($lease->tagihan_lainnya ?? [])->sum('jumlah');
            return round($harga + $ppn + $ppb + $lain, 2);
        };

        // ── L1: PT Maju Bersama (Des 2025 – Des 2026) ──────────────────────
        // Total: 12.000.000 + 11% PPN + 200.000 + 75.000 = 13.595.000
        $totalL1 = $calcTotal($l1); // 13.595.000

        Invoice::create([
            'lease_id'          => $l1->id,
            'bulan_tahun'       => 'Desember 2025',
            'tanggal_jatuh_tempo' => Carbon::create(2025, 12, 31),
            'jumlah_tagihan'    => $totalL1,
            'status_pembayaran' => 'Lunas',
            'tanggal_bayar'     => Carbon::create(2025, 12, 28),
            'catatan_pembayaran'=> 'Transfer BCA, ref #TRF-2512-001',
        ]);

        Invoice::create([
            'lease_id'          => $l1->id,
            'bulan_tahun'       => 'Januari 2026',
            'tanggal_jatuh_tempo' => Carbon::create(2026, 1, 31),
            'jumlah_tagihan'    => $totalL1,
            'status_pembayaran' => 'Lunas',
            'tanggal_bayar'     => Carbon::create(2026, 1, 29),
            'catatan_pembayaran'=> 'Transfer BCA, ref #TRF-0126-001',
        ]);

        // Jatuh tempo 5 hari dari sekarang → muncul di widget dashboard
        Invoice::create([
            'lease_id'          => $l1->id,
            'bulan_tahun'       => 'Februari 2026',
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(5)->endOfDay(),
            'jumlah_tagihan'    => $totalL1,
            'status_pembayaran' => 'Belum Bayar',
            'tanggal_bayar'     => null,
            'catatan_pembayaran'=> null,
        ]);

        // ── L2: CV Sejahtera Abadi (Jan 2026 – Jan 2027) ───────────────────
        // Total: 9.500.000 + 11% PPN = 10.545.000
        $totalL2 = $calcTotal($l2); // 10.545.000

        Invoice::create([
            'lease_id'          => $l2->id,
            'bulan_tahun'       => 'Januari 2026',
            'tanggal_jatuh_tempo' => Carbon::create(2026, 1, 31),
            'jumlah_tagihan'    => $totalL2,
            'status_pembayaran' => 'Lunas',
            'tanggal_bayar'     => Carbon::create(2026, 1, 27),
            'catatan_pembayaran'=> 'Transfer Mandiri, ref #TRF-0126-002',
        ]);

        // Jatuh tempo 3 hari dari sekarang → muncul di widget dashboard
        Invoice::create([
            'lease_id'          => $l2->id,
            'bulan_tahun'       => 'Februari 2026',
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(3)->endOfDay(),
            'jumlah_tagihan'    => $totalL2,
            'status_pembayaran' => 'Belum Bayar',
            'tanggal_bayar'     => null,
            'catatan_pembayaran'=> null,
        ]);

        // ── L3: Budi Santoso (Feb 2026 – Jul 2026) ─────────────────────────
        // Total: 2.500.000 + 11% PPN + 100.000 = 2.875.000
        $totalL3 = $calcTotal($l3); // 2.875.000

        // Jatuh tempo akhir bulan ini, baru mulai
        Invoice::create([
            'lease_id'          => $l3->id,
            'bulan_tahun'       => 'Februari 2026',
            'tanggal_jatuh_tempo' => Carbon::create(2026, 2, 28),
            'jumlah_tagihan'    => $totalL3,
            'status_pembayaran' => 'Belum Bayar',
            'tanggal_bayar'     => null,
            'catatan_pembayaran'=> null,
        ]);

        // ── L4: Toko Elektronik Rina (Agt 2025 – Feb 2026) ─────────────────
        // Total: 5.000.000 + 11% PPN + 120.000 = 5.670.000
        $totalL4 = $calcTotal($l4); // 5.670.000

        $l4Invoices = [
            ['bulan' => 'Agustus 2025',   'due' => '2025-08-31', 'status' => 'Lunas',      'bayar' => '2025-08-29', 'catatan' => 'Lunas tepat waktu'],
            ['bulan' => 'September 2025', 'due' => '2025-09-30', 'status' => 'Lunas',      'bayar' => '2025-09-28', 'catatan' => 'Lunas tepat waktu'],
            ['bulan' => 'Oktober 2025',   'due' => '2025-10-31', 'status' => 'Lunas',      'bayar' => '2025-10-30', 'catatan' => 'Lunas tepat waktu'],
            ['bulan' => 'November 2025',  'due' => '2025-11-30', 'status' => 'Terlambat',  'bayar' => null,         'catatan' => 'Belum ada konfirmasi pembayaran'],
            ['bulan' => 'Desember 2025',  'due' => '2025-12-31', 'status' => 'Terlambat',  'bayar' => null,         'catatan' => null],
            ['bulan' => 'Januari 2026',   'due' => '2026-01-31', 'status' => 'Terlambat',  'bayar' => null,         'catatan' => null],
            ['bulan' => 'Februari 2026',  'due' => '2026-02-28', 'status' => 'Belum Bayar', 'bayar' => null,        'catatan' => 'Kontrak berakhir akhir bulan ini'],
        ];

        foreach ($l4Invoices as $inv) {
            Invoice::create([
                'lease_id'           => $l4->id,
                'bulan_tahun'        => $inv['bulan'],
                'tanggal_jatuh_tempo'=> Carbon::parse($inv['due']),
                'jumlah_tagihan'     => $totalL4,
                'status_pembayaran'  => $inv['status'],
                'tanggal_bayar'      => $inv['bayar'] ? Carbon::parse($inv['bayar']) : null,
                'catatan_pembayaran' => $inv['catatan'],
            ]);
        }

        // ── L5: PT Nusantara Logistik (Jan 2025 – Jan 2026) ────────────────
        // Total: 15.000.000 + 11% PPN + 350.000 + 100.000 + 50.000 = 17.165.000
        $totalL5 = $calcTotal($l5); // 17.165.000

        $l5Invoices = [
            ['bulan' => 'Januari 2025',   'due' => '2025-01-31', 'status' => 'Lunas',     'bayar' => '2025-01-30'],
            ['bulan' => 'Februari 2025',  'due' => '2025-02-28', 'status' => 'Lunas',     'bayar' => '2025-02-27'],
            ['bulan' => 'Maret 2025',     'due' => '2025-03-31', 'status' => 'Lunas',     'bayar' => '2025-03-28'],
            ['bulan' => 'April 2025',     'due' => '2025-04-30', 'status' => 'Lunas',     'bayar' => '2025-04-29'],
            ['bulan' => 'Mei 2025',       'due' => '2025-05-31', 'status' => 'Lunas',     'bayar' => '2025-05-30'],
            ['bulan' => 'Juni 2025',      'due' => '2025-06-30', 'status' => 'Lunas',     'bayar' => '2025-06-28'],
            ['bulan' => 'Juli 2025',      'due' => '2025-07-31', 'status' => 'Lunas',     'bayar' => '2025-07-30'],
            ['bulan' => 'Agustus 2025',   'due' => '2025-08-31', 'status' => 'Lunas',     'bayar' => '2025-08-29'],
            ['bulan' => 'September 2025', 'due' => '2025-09-30', 'status' => 'Lunas',     'bayar' => '2025-09-26'],
            ['bulan' => 'Oktober 2025',   'due' => '2025-10-31', 'status' => 'Lunas',     'bayar' => '2025-10-31'],
            ['bulan' => 'November 2025',  'due' => '2025-11-30', 'status' => 'Lunas',     'bayar' => '2025-11-28'],
            ['bulan' => 'Desember 2025',  'due' => '2025-12-31', 'status' => 'Lunas',     'bayar' => '2025-12-30'],
            ['bulan' => 'Januari 2026',   'due' => '2026-01-31', 'status' => 'Terlambat', 'bayar' => null],
        ];

        foreach ($l5Invoices as $inv) {
            Invoice::create([
                'lease_id'           => $l5->id,
                'bulan_tahun'        => $inv['bulan'],
                'tanggal_jatuh_tempo'=> Carbon::parse($inv['due']),
                'jumlah_tagihan'     => $totalL5,
                'status_pembayaran'  => $inv['status'],
                'tanggal_bayar'      => $inv['bayar'] ? Carbon::parse($inv['bayar']) : null,
                'catatan_pembayaran' => $inv['status'] === 'Terlambat' ? 'Belum dibayar saat kontrak berakhir' : null,
            ]);
        }

        $this->command->info('🧾  Invoice dibuat:');
        $this->command->info('    L1 PT Maju Bersama      → 3 invoice (2 Lunas, 1 Belum Bayar - jatuh tempo 5 hari)');
        $this->command->info('    L2 CV Sejahtera Abadi   → 2 invoice (1 Lunas, 1 Belum Bayar - jatuh tempo 3 hari)');
        $this->command->info('    L3 Budi Santoso         → 1 invoice (Belum Bayar)');
        $this->command->info('    L4 Toko Elektronik Rina → 7 invoice (3 Lunas, 3 Terlambat, 1 Belum Bayar)');
        $this->command->info('    L5 PT Nusantara Logistik→ 13 invoice (12 Lunas, 1 Terlambat)');
        $this->command->line('');
        $this->command->info('✅  Seeder selesai! Total: 5 properti, 5 penyewa, 5 kontrak, 26 invoice.');
        $this->command->line('');
        $this->command->warn('📌  Yang muncul di widget Dashboard (jatuh tempo ≤1 bulan, Belum Bayar):');
        $this->command->warn('    - PT Maju Bersama / BLG-A01 / Feb 2026  (5 hari lagi)');
        $this->command->warn('    - CV Sejahtera Abadi / BLG-A02 / Feb 2026 (3 hari lagi)');
    }
}
