<?php

namespace Tests\Unit;

use App\Filament\Widgets\RevenueProjectionWidget;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueProjectionYearTest extends TestCase
{
    use RefreshDatabase;

    private RevenueProjectionWidget $widget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widget = new RevenueProjectionWidget();
    }

    private function makeLease(Carbon $start): Lease
    {
        static $i = 0;
        $i++;
        $tenant   = Tenant::create(['nama' => "T{$i}", 'kontak' => "08{$i}", 'email' => "t{$i}@t.com", 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => "P{$i}", 'nama' => "Prop {$i}", 'status' => 'Disewa']);

        return Lease::create([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => $start,
            'tanggal_akhir'   => $start->copy()->addYear(),
            'periode'         => '1 tahun',
            'harga_sewa'      => 5000000,
            'ppn_persen'      => 0,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ]);
    }

    // Akses protected method via Reflection
    private function projectionYear(Lease $lease): int
    {
        $ref = new \ReflectionMethod(RevenueProjectionWidget::class, 'getProjectionYear');
        $ref->setAccessible(true);
        return $ref->invoke($this->widget, $lease);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function january_start_maps_to_same_year(): void
    {
        $lease = $this->makeLease(Carbon::create(2026, 1, 1));
        $this->assertEquals(2026, $this->projectionYear($lease));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function june_start_maps_to_same_year(): void
    {
        $lease = $this->makeLease(Carbon::create(2026, 6, 1));
        $this->assertEquals(2026, $this->projectionYear($lease));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function july_start_maps_to_next_year(): void
    {
        $lease = $this->makeLease(Carbon::create(2026, 7, 1));
        $this->assertEquals(2027, $this->projectionYear($lease));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function december_start_maps_to_next_year(): void
    {
        $lease = $this->makeLease(Carbon::create(2025, 12, 1));
        $this->assertEquals(2026, $this->projectionYear($lease));
    }
}
