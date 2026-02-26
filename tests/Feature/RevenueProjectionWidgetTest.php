<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueProjectionWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function makeLease(Carbon $start, Carbon $end, float $harga, array $tagihan = [], float $ppn = 0): Lease
    {
        static $i = 0;
        $i++;
        $tenant   = Tenant::create(['nama' => "T{$i}", 'kontak' => "08{$i}", 'email' => "t{$i}@t.com", 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => "P{$i}", 'nama' => "Prop {$i}", 'status' => 'Disewa']);

        return Lease::create([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => $start,
            'tanggal_akhir'   => $end,
            'periode'         => '1 tahun',
            'harga_sewa'      => $harga,
            'ppn_persen'      => $ppn,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => empty($tagihan) ? null : $tagihan,
        ]);
    }

    private function makeInvoice(Lease $lease, string $status, int $year, float $jumlah): Invoice
    {
        return Invoice::create([
            'lease_id'           => $lease->id,
            'bulan_tahun'        => "Januari {$year}",
            'tanggal_jatuh_tempo'=> Carbon::create($year, 1, 31),
            'jumlah_tagihan'     => $jumlah,
            'status_pembayaran'  => $status,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lease_starting_july_or_later_is_excluded_from_current_year_projection(): void
    {
        $year = now()->year;

        // Mulai Juli tahun ini → seharusnya masuk proyeksi tahun depan
        $this->makeLease(
            Carbon::create($year, 7, 1),
            Carbon::create($year + 1, 6, 30),
            10000000
        );

        $widget = new \App\Filament\Widgets\RevenueProjectionWidget();
        $stats  = $this->invokeGetStats($widget);

        // Proyeksi tahun ini harus 0 karena satu-satunya kontrak digeser ke tahun depan
        $this->assertStringContainsString('Rp 0', $stats[0]->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lease_starting_january_to_june_is_included_in_current_year_projection(): void
    {
        $year = now()->year;

        // Mulai Maret tahun ini, 10 bulan, harga 10jt tanpa PPN
        $this->makeLease(
            Carbon::create($year, 3, 1),
            Carbon::create($year, 12, 31),
            10000000
        );

        $widget = new \App\Filament\Widgets\RevenueProjectionWidget();
        $stats  = $this->invokeGetStats($widget);

        // 10 bulan × 10.000.000 = 100.000.000
        $this->assertStringContainsString('100.000.000', $stats[0]->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function collected_stat_only_counts_lunas_invoices_in_current_year(): void
    {
        $year  = now()->year;
        $lease = $this->makeLease(
            Carbon::create($year, 1, 1),
            Carbon::create($year, 12, 31),
            10000000
        );

        $this->makeInvoice($lease, 'Lunas',      $year, 10000000);
        $this->makeInvoice($lease, 'Belum Bayar', $year, 10000000);
        $this->makeInvoice($lease, 'Terlambat',   $year, 10000000);

        $widget = new \App\Filament\Widgets\RevenueProjectionWidget();
        $stats  = $this->invokeGetStats($widget);

        // Sudah Masuk hanya 1 invoice Lunas = 10.000.000
        $this->assertStringContainsString('10.000.000', $stats[1]->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function outstanding_stat_counts_belum_bayar_and_terlambat(): void
    {
        $year  = now()->year;
        $lease = $this->makeLease(
            Carbon::create($year, 1, 1),
            Carbon::create($year, 12, 31),
            10000000
        );

        $this->makeInvoice($lease, 'Belum Bayar', $year, 5000000);
        $this->makeInvoice($lease, 'Terlambat',   $year, 5000000);

        $widget = new \App\Filament\Widgets\RevenueProjectionWidget();
        $stats  = $this->invokeGetStats($widget);

        // Belum Masuk = 5.000.000 + 5.000.000 = 10.000.000
        $this->assertStringContainsString('10.000.000', $stats[2]->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lunas_invoices_from_previous_year_are_not_counted(): void
    {
        $year  = now()->year;
        $lease = $this->makeLease(
            Carbon::create($year - 1, 1, 1),
            Carbon::create($year, 12, 31),
            10000000
        );

        // Invoice dari tahun lalu, Lunas — tidak boleh masuk "Sudah Masuk" tahun ini
        $this->makeInvoice($lease, 'Lunas', $year - 1, 10000000);

        $widget = new \App\Filament\Widgets\RevenueProjectionWidget();
        $stats  = $this->invokeGetStats($widget);

        $this->assertStringContainsString('Rp 0', $stats[1]->getValue());
    }

    // ── Helper: invoke protected getStats() ──────────────────────────────────

    private function invokeGetStats(\App\Filament\Widgets\RevenueProjectionWidget $widget): array
    {
        $ref = new \ReflectionMethod(\App\Filament\Widgets\RevenueProjectionWidget::class, 'getStats');
        $ref->setAccessible(true);
        return $ref->invoke($widget);
    }
}
