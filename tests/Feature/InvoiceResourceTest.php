<?php

namespace Tests\Feature;

use App\Filament\Resources\InvoiceResource\Pages\EditInvoice;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());

        $tenant   = Tenant::create(['nama' => 'PT Test', 'kontak' => '081', 'email' => 't@t.com', 'alamat' => '-']);
        $property = Property::create(['kode_lokasi' => 'INV-01', 'nama' => 'Prop Test', 'status' => 'Disewa']);
        $lease    = Lease::create([
            'tenant_id'       => $tenant->id,
            'property_id'     => $property->id,
            'tanggal_mulai'   => Carbon::create(2026, 1, 1),
            'tanggal_akhir'   => Carbon::create(2026, 12, 31),
            'periode'         => '1 tahun',
            'harga_sewa'      => 10000000,
            'ppn_persen'      => 11,
            'ppb_persen'      => 0,
            'tagihan_lainnya' => null,
        ]);

        $this->invoice = Invoice::create([
            'lease_id'           => $lease->id,
            'bulan_tahun'        => 'Januari 2026',
            'tanggal_jatuh_tempo'=> Carbon::create(2026, 1, 31),
            'jumlah_tagihan'     => 11100000,
            'status_pembayaran'  => 'Belum Bayar',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function list_page_renders_and_shows_invoice(): void
    {
        Livewire::test(ListInvoices::class)
            ->assertSuccessful()
            ->assertSee('Januari 2026');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_update_status_to_lunas(): void
    {
        Livewire::test(EditInvoice::class, ['record' => $this->invoice->id])
            ->fillForm([
                'status_pembayaran' => 'Lunas',
                'tanggal_bayar'     => '2026-01-28',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', [
            'id'                => $this->invoice->id,
            'status_pembayaran' => 'Lunas',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_update_status_to_terlambat(): void
    {
        Livewire::test(EditInvoice::class, ['record' => $this->invoice->id])
            ->fillForm(['status_pembayaran' => 'Terlambat'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', [
            'id'                => $this->invoice->id,
            'status_pembayaran' => 'Terlambat',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_save_catatan_pembayaran(): void
    {
        Livewire::test(EditInvoice::class, ['record' => $this->invoice->id])
            ->fillForm([
                'status_pembayaran'  => 'Lunas',
                'catatan_pembayaran' => 'Transfer via BCA',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', [
            'id'                 => $this->invoice->id,
            'catatan_pembayaran' => 'Transfer via BCA',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function status_pembayaran_is_required(): void
    {
        Livewire::test(EditInvoice::class, ['record' => $this->invoice->id])
            ->fillForm(['status_pembayaran' => null])
            ->call('save')
            ->assertHasFormErrors(['status_pembayaran']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_is_null_after_marked_lunas(): void
    {
        $this->invoice->update(['status_pembayaran' => 'Lunas', 'tanggal_bayar' => now()]);

        $this->assertNull($this->invoice->fresh()->sisa_hari);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sisa_hari_is_integer_when_belum_bayar(): void
    {
        $this->invoice->update([
            'status_pembayaran'  => 'Belum Bayar',
            'tanggal_jatuh_tempo'=> Carbon::today()->addDays(4),
        ]);

        $this->assertIsInt($this->invoice->fresh()->sisa_hari);
        $this->assertEquals(4, $this->invoice->fresh()->sisa_hari);
    }
}
