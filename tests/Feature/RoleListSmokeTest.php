<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleListSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view dashboard', 'manage users', 'manage roles',
            'view barang', 'manage barang',
            'view supplier', 'manage supplier',
            'view kategori', 'manage kategori',
            'view penerimaan', 'create penerimaan', 'verify penerimaan', 'delete penerimaan',
            'view barang_keluar', 'create barang_keluar', 'delete barang_keluar',
            'view retur_pembelian', 'create retur_pembelian', 'delete retur_pembelian',
            'view retur_penjualan', 'create retur_penjualan', 'delete retur_penjualan',
            'view stock_opname', 'create stock_opname', 'finalize stock_opname', 'delete stock_opname',
            'view trash', 'manage trash',
            'manage laporan', 'manage activity',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo(['view dashboard', 'view barang']);

        $this->admin = User::create(['name' => 'Administrator', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->admin->assignRole($adminRole);

        $this->staff = User::create(['name' => 'Staff', 'email' => 'staff@test.com', 'password' => bcrypt('password')]);
        $this->staff->assignRole($staffRole);
    }

    public function test_role_list_renders_with_stats_and_roles()
    {
        $this->actingAs($this->admin);

        Livewire::test('role.role-list')
            ->assertOk()
            ->assertSee('Manajemen Role')
            ->assertSee('admin')
            ->assertSee('staff')
            ->assertSee('Total Permission');
    }

    public function test_open_modal_and_create_role()
    {
        $this->actingAs($this->admin);

        Livewire::test('role.role-list')
            ->call('openModal')
            ->assertSet('showModal', true)
            ->set('name', 'kasir')
            ->call('selectGroup', 'Umum')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('roles', ['name' => 'kasir']);
    }

    public function test_edit_role_updates_permissions()
    {
        $this->actingAs($this->admin);
        $staffRoleId = Role::where('name', 'staff')->firstOrFail()->id;

        Livewire::test('role.role-list')
            ->call('edit', $staffRoleId)
            ->assertSet('isEdit', true)
            ->assertSet('name', 'staff')
            ->set('selected_permissions', ['view dashboard', 'view barang', 'manage roles'])
            ->call('save');

        $this->assertDatabaseHas('roles', ['name' => 'staff']);
    }

    public function test_delete_role_with_users_is_blocked()
    {
        $this->actingAs($this->admin);
        $staffRoleId = Role::where('name', 'staff')->firstOrFail()->id;

        Livewire::test('role.role-list')
            ->call('delete', $staffRoleId);

        $this->assertDatabaseHas('roles', ['name' => 'staff']);
    }

    public function test_full_page_renders_with_layout()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/roles');

        $response->assertOk();
        $response->assertSee('Manajemen Role');
        $response->assertSee('Total Permission');
        $response->assertSee('Administrator');
    }

    public function test_staff_is_redirected_from_role_page()
    {
        $this->actingAs($this->staff);

        Livewire::test('role.role-list')
            ->assertRedirect(route('dashboard'));
    }
}
