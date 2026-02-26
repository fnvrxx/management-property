<?php

namespace Tests\Feature;

use App\Filament\Resources\TenantResource;
use App\Filament\Resources\TenantResource\Pages\CreateTenant;
use App\Filament\Resources\TenantResource\Pages\EditTenant;
use App\Filament\Resources\TenantResource\Pages\ListTenants;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function list_page_renders_successfully(): void
    {
        Tenant::create(['nama' => 'PT Test', 'kontak' => '081', 'email' => 'pt@test.com', 'alamat' => 'Jakarta']);

        Livewire::test(ListTenants::class)
            ->assertSuccessful()
            ->assertSee('PT Test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_create_tenant(): void
    {
        Livewire::test(CreateTenant::class)
            ->fillForm([
                'nama'    => 'CV Baru',
                'kontak'  => '08211111111',
                'email'   => 'baru@cv.com',
                'alamat'  => 'Bandung',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tenants', ['nama' => 'CV Baru', 'email' => 'baru@cv.com']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_requires_nama_and_kontak(): void
    {
        Livewire::test(CreateTenant::class)
            ->fillForm(['nama' => '', 'kontak' => ''])
            ->call('create')
            ->assertHasFormErrors(['nama', 'kontak']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_edit_tenant(): void
    {
        $tenant = Tenant::create(['nama' => 'Lama', 'kontak' => '081', 'email' => 'lama@test.com', 'alamat' => '-']);

        Livewire::test(EditTenant::class, ['record' => $tenant->id])
            ->fillForm(['nama' => 'Baru', 'kontak' => '082'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'nama' => 'Baru']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_delete_tenant(): void
    {
        $tenant = Tenant::create(['nama' => 'Hapus Ini', 'kontak' => '081', 'email' => 'hapus@test.com', 'alamat' => '-']);

        Livewire::test(ListTenants::class)
            ->callTableAction('delete', $tenant)
            ->assertSuccessful();

        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function list_is_searchable_by_nama(): void
    {
        Tenant::create(['nama' => 'PT Alpha', 'kontak' => '081', 'email' => 'alpha@test.com', 'alamat' => '-']);
        Tenant::create(['nama' => 'CV Beta',  'kontak' => '082', 'email' => 'beta@test.com',  'alamat' => '-']);

        Livewire::test(ListTenants::class)
            ->searchTable('Alpha')
            ->assertCanSeeTableRecords(Tenant::where('nama', 'PT Alpha')->get())
            ->assertCanNotSeeTableRecords(Tenant::where('nama', 'CV Beta')->get());
    }
}
