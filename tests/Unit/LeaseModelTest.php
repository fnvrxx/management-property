<?php

namespace Tests\Unit;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeLease(array $attrs = []): Lease
    {
        $tenant   = Tenant::create(['nama' => 'Tenant A', 'kontak' => '081', 'email' => 'a@a.com', 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => 'TST-01', 'nama' => 'Prop A', 'status' => 'Disewa']);

        return Lease::create(array_merge([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => Carbon::create(2026, 1, 1),
            'tanggal_akhir'   => Carbon::create(2026, 12, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 5000000,
            'ppn_persen'      => 11,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ], $attrs));
    }

    // ── JSON cast tagihan_lainnya ─────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function tagihan_lainnya_is_cast_to_array(): void
    {
        $lease = $this->makeLease([
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 150000],
                ['nama' => 'Keamanan', 'jumlah' => 50000],
            ],
        ]);

        $fresh = $lease->fresh();

        $this->assertIsArray($fresh->tagihan_lainnya);
        $this->assertCount(2, $fresh->tagihan_lainnya);
        $this->assertEquals('Listrik', $fresh->tagihan_lainnya[0]['nama']);
        $this->assertEquals(150000, $fresh->tagihan_lainnya[0]['jumlah']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tagihan_lainnya_is_null_when_not_set(): void
    {
        $lease = $this->makeLease(['tagihan_lainnya' => null]);

        $this->assertNull($lease->fresh()->tagihan_lainnya);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tagihan_lainnya_sum_is_correct(): void
    {
        $lease = $this->makeLease([
            'tagihan_lainnya' => [
                ['nama' => 'Listrik', 'jumlah' => 200000],
                ['nama' => 'IPL',     'jumlah' => 100000],
            ],
        ]);

        $sum = collect($lease->fresh()->tagihan_lainnya)->sum('jumlah');
        $this->assertEquals(300000, $sum);
    }

    // ── Date cast ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function tanggal_mulai_and_akhir_are_cast_to_carbon(): void
    {
        $lease = $this->makeLease();
        $fresh = $lease->fresh();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->tanggal_mulai);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->tanggal_akhir);
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function lease_belongs_to_tenant_and_property(): void
    {
        $lease = $this->makeLease();

        $this->assertEquals('Tenant A', $lease->tenant->nama);
        $this->assertEquals('TST-01', $lease->property->kode_lokasi);
    }
}
