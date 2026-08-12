<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserListSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['manage users', 'view trash'];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);

        $this->admin = User::create(['name' => 'Administrator', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->admin->assignRole($adminRole);

        $this->staff = User::create(['name' => 'Staff Gudang', 'email' => 'staff@test.com', 'password' => bcrypt('password')]);
        $this->staff->assignRole($staffRole);
    }

    public function test_user_list_renders_with_stats_and_users()
    {
        $this->actingAs($this->admin);

        Livewire::test('user.user-list')
            ->assertOk()
            ->assertSee('Total User')
            ->assertSee('Administrator')
            ->assertSee('Staff Gudang');
    }

    public function test_open_modal_and_create_user()
    {
        $this->actingAs($this->admin);

        Livewire::test('user.user-list')
            ->call('openModal')
            ->assertSet('showModal', true)
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@test.com')
            ->set('password', 'secret123')
            ->set('selected_roles', ['staff'])
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('users', ['name' => 'Budi Santoso', 'email' => 'budi@test.com']);
    }

    public function test_edit_user_updates_data()
    {
        $this->actingAs($this->admin);

        Livewire::test('user.user-list')
            ->call('edit', $this->staff->id)
            ->assertSet('isEdit', true)
            ->set('name', 'Staff Gudang Baru')
            ->call('save');

        $this->assertDatabaseHas('users', ['name' => 'Staff Gudang Baru']);
    }

    public function test_cannot_delete_own_account()
    {
        $this->actingAs($this->admin);

        Livewire::test('user.user-list')
            ->call('delete', $this->admin->id);

        $this->assertDatabaseHas('users', ['name' => 'Administrator']);
    }

    public function test_staff_is_redirected_from_user_page()
    {
        $this->actingAs($this->staff);

        Livewire::test('user.user-list')
            ->assertRedirect(route('dashboard'));
    }

    public function test_search_filters_users()
    {
        $this->actingAs($this->admin);

        Livewire::test('user.user-list')
            ->set('search', 'budi')
            ->assertOk();
    }

    public function test_full_page_renders_with_layout()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/users');

        $response->assertOk();
        $response->assertSee('Manajemen User');
        $response->assertSee('Administrator');
    }
}
