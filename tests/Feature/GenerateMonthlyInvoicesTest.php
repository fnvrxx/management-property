<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateMonthlyInvoicesTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveLease(array $attrs = []): Lease
    {
        $tenant   = Tenant::create(['nama' => 'Penyewa Test', 'kontak' => '081', 'email' => 'x@x.com', 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => 'GEN-01', 'nama' => 'Prop Test', 'status' => 'Disewa']);

        return Lease::create(array_merge([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => now()->subMonths(1)->startOfMonth(),
            'tanggal_akhir'   => now()->addMonths(11)->endOfMonth(),
            'periode'         => '1 tahun',
            'harga_sewa'      => 10000000,
            'ppn_persen'      => 11,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ], $attrs));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_generates_invoice_for_active_lease(): void
    {
        $this->createActiveLease();

        $this->artisan('invoices:generate')->assertSuccessful();

        $this->assertGreaterThan(0, Invoice::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_does_not_duplicate_invoice_for_same_month(): void
    {
        $this->createActiveLease();

        $this->artisan('invoices:generate');
        $countAfterFirst = Invoice::count();

        $this->artisan('invoices:generate');
        $countAfterSecond = Invoice::count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_sets_status_belum_bayar_on_new_invoice(): void
    {
        $this->createActiveLease();

        $this->artisan('invoices:generate');

        $invoice = Invoice::first();
        $this->assertEquals('Belum Bayar', $invoice->status_pembayaran);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_includes_tagihan_lainnya_in_total(): void
    {
        $this->createActiveLease([
            'harga_sewa'      => 10000000,
            'ppn_persen'      => 0,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 200000],
                ['nama' => 'IPL',     'jumlah' => 100000],
            ],
        ]);

        $this->artisan('invoices:generate');

        $invoice = Invoice::first();
        // 10.000.000 + 200.000 + 100.000 = 10.300.000
        $this->assertEquals(10300000, (float) $invoice->jumlah_tagihan);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_calculates_ppn_correctly(): void
    {
        $this->createActiveLease([
            'harga_sewa'  => 10000000,
            'ppn_persen'  => 11,
            'ppb_persen'  => 0,
            'tagihan_lainnya' => null,
        ]);

        $this->artisan('invoices:generate');

        $invoice = Invoice::first();
        // 10.000.000 + 11% = 11.100.000
        $this->assertEquals(11100000, (float) $invoice->jumlah_tagihan);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_skips_expired_lease(): void
    {
        $tenant   = Tenant::create(['nama' => 'Expired', 'kontak' => '082', 'email' => 'e@e.com', 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => 'EXP-01', 'nama' => 'Expired Prop', 'status' => 'Tersedia']);

        Lease::create([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => now()->subMonths(6),
            'tanggal_akhir'   => now()->subMonths(1), // sudah berakhir
            'periode'         => '5 bulan',
            'harga_sewa'      => 5000000,
            'ppn_persen'      => 11,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ]);

        $this->artisan('invoices:generate');

        $this->assertEquals(0, Invoice::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function command_shows_warning_when_no_active_leases(): void
    {
        $this->artisan('invoices:generate')
            ->expectsOutputToContain('Tidak ada kontrak aktif');
    }
}
