<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Tenant;
use App\Models\Lease;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertyManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel terkait (opsional, hati-hati di production!)
        DB::table('invoices')->delete();
        DB::table('leases')->delete();
        DB::table('properties')->delete();
        DB::table('tenants')->delete();

        // === 1. Buat Penyewa (Tenants) ===
        $tenants = [
            ['nama' => 'PT Maju Bersama', 'kontak' => '081234567890', 'email' => 'maju@example.com'],
            ['nama' => 'CV Sejahtera Abadi', 'kontak' => '082345678901', 'email' => 'sejahtera@example.com'],
            ['nama' => 'Budi Santoso', 'kontak' => '083456789012', 'email' => 'budi@example.com'],
        ];

        foreach ($tenants as $data) {
            Tenant::create($data);
        }

        // === 2. Buat Properti ===
        $properties = [
            ['kode_lokasi' => 'BLG-A01', 'nama' => 'Gedung A Lantai 1', 'status' => 'Disewa'],
            ['kode_lokasi' => 'BLG-A02', 'nama' => 'Gedung A Lantai 2', 'status' => 'Disewa'],
            ['kode_lokasi' => 'KIOS-10', 'nama' => 'Kios Pasar Pagi No.10', 'status' => 'Disewa'],
            ['kode_lokasi' => 'BLG-B01', 'nama' => 'Gedung B Lantai 1', 'status' => 'Tersedia'], // tidak disewa
        ];

        foreach ($properties as $data) {
            Property::create($data);
        }

        // === 3. Buat Kontrak Sewa Aktif (Leases) ===
        $leases = [
            [
                'tenant_id' => 1,
                'property_id' => 1,
                'tanggal_mulai' => Carbon::now()->subMonths(2)->startOfMonth(), // 1 Des 2025
                'tanggal_akhir' => Carbon::now()->addMonths(10)->endOfMonth(),  // 31 Des 2026
                'periode' => '1 tahun',
                'harga_sewa' => 10000000,
                'ppn_persen' => 11.00,
                'ppb_persen' => 0.00,
                'tagihan_lainnya' => json_encode([
                    ['nama' => 'Listrik', 'jumlah' => 150000],
                    ['nama' => 'Keamanan', 'jumlah' => 50000]
                ]),
            ],
            [
                'tenant_id' => 2,
                'property_id' => 2,
                'tanggal_mulai' => Carbon::now()->subMonths(1)->startOfMonth(), // 1 Jan 2026
                'tanggal_akhir' => Carbon::now()->addMonths(11)->endOfMonth(),  // 31 Jan 2027
                'periode' => '1 tahun',
                'harga_sewa' => 8500000,
                'ppn_persen' => 11.00,
                'ppb_persen' => 0.00,
                'tagihan_lainnya' => null,
            ],
            [
                'tenant_id' => 3,
                'property_id' => 3,
                'tanggal_mulai' => Carbon::now()->startOfMonth(), // 1 Feb 2026
                'tanggal_akhir' => Carbon::now()->addMonths(5)->endOfMonth(), // 31 Jul 2026
                'periode' => '6 bulan',
                'harga_sewa' => 3000000,
                'ppn_persen' => 11.00,
                'ppb_persen' => 0.00,
                'tagihan_lainnya' => json_encode([
                    ['nama' => 'IPL', 'jumlah' => 75000]
                ]),
            ],
        ];

        foreach ($leases as $lease) {
            Lease::create($lease);
        }

        $this->command->info('✅ Seeder: 3 penyewa, 4 properti, 3 kontrak aktif berhasil dibuat!');
    }
}