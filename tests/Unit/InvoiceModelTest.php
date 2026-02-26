<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeLease(array $leaseAttrs = []): Lease
    {
        $tenant   = Tenant::create(['nama' => 'Test Tenant', 'kontak' => '08111', 'email' => 'test@test.com', 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => 'TST-01', 'nama' => 'Test Property', 'status' => 'Disewa']);

        return Lease::create(array_merge([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => Carbon::create(2026, 1, 1),
            'tanggal_akhir'   => Carbon::create(2026, 12, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 10000000,
            'ppn_persen'      => 11,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ], $leaseAttrs));
    }

    private function makeInvoice(Lease $lease, array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'lease_id'           => $lease->id,
            'bulan_tahun'        => 'Januari 2026',
            'tanggal_jatuh_tempo'=> Carbon::create(2026, 1, 31),
            'jumlah_tagihan'     => 11100000,
            'status_pembayaran'  => 'Belum Bayar',
        ], $attrs));
    }

    // ── sisa_hari ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_returns_null_when_status_is_lunas(): void
    {
        $lease   = $this->makeLease();
        $invoice = $this->makeInvoice($lease, [
            'status_pembayaran' => 'Lunas',
            'tanggal_jatuh_tempo' => Carbon::tomorrow(),
        ]);

        $this->assertNull($invoice->sisa_hari);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_returns_integer_when_not_lunas(): void
    {
        $lease   = $this->makeLease();
        $invoice = $this->makeInvoice($lease, [
            'status_pembayaran'  => 'Belum Bayar',
            'tanggal_jatuh_tempo'=> Carbon::today()->addDays(5),
        ]);

        $this->assertIsInt($invoice->sisa_hari);
        $this->assertEquals(5, $invoice->sisa_hari);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_returns_negative_when_overdue(): void
    {
        $lease   = $this->makeLease();
        $invoice = $this->makeInvoice($lease, [
            'status_pembayaran'  => 'Terlambat',
            'tanggal_jatuh_tempo'=> Carbon::now()->subDays(3),
        ]);

        $this->assertIsInt($invoice->sisa_hari);
        $this->assertEquals(-3, $invoice->sisa_hari);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_returns_null_for_terlambat_that_is_paid(): void
    {
        // Jika user mengubah status dari Terlambat ke Lunas, sisa_hari harus null
        $lease   = $this->makeLease();
        $invoice = $this->makeInvoice($lease, [
            'status_pembayaran'  => 'Lunas',
            'tanggal_jatuh_tempo'=> Carbon::now()->subDays(10),
            'tanggal_bayar'      => Carbon::now()->subDays(2),
        ]);

        $this->assertNull($invoice->sisa_hari);
    }
}
