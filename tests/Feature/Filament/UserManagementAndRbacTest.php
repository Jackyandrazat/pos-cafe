<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\KitchenDisplay;
use App\Filament\Pages\TableStatusBoard;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PaymentAccountResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup default roles
        Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'owner'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kitchen'], ['guard_name' => 'web']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->withoutMiddleware(\App\Http\Middleware\AdminAccessCodeMiddleware::class);
        $this->withSession([\App\Http\Middleware\AdminAccessCodeMiddleware::SESSION_KEY => true]);
    }

    public function test_first_staff_user_created_automatically_gets_admin_role(): void
    {
        // Pastikan tidak ada user staf
        $this->assertEquals(0, User::where('is_guest', false)->whereNull('customer_id')->count());

        $firstUser = User::create([
            'name' => 'Founding Admin',
            'email' => 'founder@poscafe.test',
            'password' => 'secret123',
            'is_guest' => false,
        ]);

        $this->assertTrue($firstUser->hasRole('admin'));
        $this->assertTrue($firstUser->isAdmin());

        // Buat user kedua tanpa role
        $secondUser = User::create([
            'name' => 'Second Staff',
            'email' => 'second@poscafe.test',
            'password' => 'secret123',
            'is_guest' => false,
        ]);

        $this->assertFalse($secondUser->hasRole('admin'));
    }

    public function test_admin_and_owner_can_access_user_resource(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $owner = User::factory()->create();
        $owner->roles()->attach(Role::where('name', 'owner')->first());

        $this->withoutExceptionHandling();
        $response = $this->actingAs($admin)->get(UserResource::getUrl('index'));
        $response->assertSuccessful();

        $this->actingAs($owner)
            ->get(UserResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_cashier_and_kitchen_cannot_access_user_resource(): void
    {
        $admin = User::factory()->create(); // First user -> gets admin

        $kasir = User::factory()->create();
        $kasir->roles()->attach(Role::where('name', 'kasir')->first());

        $kitchen = User::factory()->create();
        $kitchen->roles()->attach(Role::where('name', 'kitchen')->first());

        $this->actingAs($kasir)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($kitchen)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_kitchen_role_can_access_kds_but_forbidden_from_orders_and_payments(): void
    {
        $admin = User::factory()->create(); // Founding Admin

        $kitchen = User::factory()->create();
        $kitchen->roles()->attach(Role::where('name', 'kitchen')->first());

        // Dapat mengakses Kitchen Display
        $this->actingAs($kitchen)
            ->get(KitchenDisplay::getUrl())
            ->assertSuccessful();

        // Dilarang mengakses Pesanan dan Pembayaran
        $this->actingAs($kitchen)
            ->get(OrderResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($kitchen)
            ->get(PaymentResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_cashier_role_can_access_pos_operations_but_forbidden_from_admin_accounts(): void
    {
        $admin = User::factory()->create(); // Founding Admin

        $kasir = User::factory()->create();
        $kasir->roles()->attach(Role::where('name', 'kasir')->first());

        // Boleh mengakses Table Status Board, Orders, Payments
        $this->actingAs($kasir)
            ->get(TableStatusBoard::getUrl())
            ->assertSuccessful();

        $this->actingAs($kasir)
            ->get(OrderResource::getUrl('index'))
            ->assertSuccessful();

        $this->actingAs($kasir)
            ->get(PaymentResource::getUrl('index'))
            ->assertSuccessful();

        // Dilarang mengakses Rekening & E-Wallet master dan Manajemen Pengguna
        $this->actingAs($kasir)
            ->get(PaymentAccountResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
            ->callTableAction(\Filament\Tables\Actions\DeleteAction::class, $admin);

        // Pastikan akun admin tidak terhapus
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }
}
