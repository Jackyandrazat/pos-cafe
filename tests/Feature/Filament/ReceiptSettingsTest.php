<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ReceiptSettings;
use App\Helpers\SettingHelper;
use App\Http\Middleware\AdminAccessCodeMiddleware;
use App\Models\Category;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'owner'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kitchen'], ['guard_name' => 'web']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->withoutMiddleware(AdminAccessCodeMiddleware::class);
        $this->withSession([AdminAccessCodeMiddleware::SESSION_KEY => true]);
    }

    public function test_admin_and_owner_can_access_receipt_settings_page(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $owner = User::factory()->create();
        $owner->roles()->attach(Role::where('name', 'owner')->first());

        $this->actingAs($admin)
            ->get(ReceiptSettings::getUrl())
            ->assertSuccessful();

        $this->actingAs($owner)
            ->get(ReceiptSettings::getUrl())
            ->assertSuccessful();
    }

    public function test_cashier_and_kitchen_cannot_access_receipt_settings_page(): void
    {
        $admin = User::factory()->create(); // Founding Admin

        $kasir = User::factory()->create();
        $kasir->roles()->attach(Role::where('name', 'kasir')->first());

        $kitchen = User::factory()->create();
        $kitchen->roles()->attach(Role::where('name', 'kitchen')->first());

        $this->actingAs($kasir)
            ->get(ReceiptSettings::getUrl())
            ->assertForbidden();

        $this->actingAs($kitchen)
            ->get(ReceiptSettings::getUrl())
            ->assertForbidden();
    }

    public function test_saving_receipt_settings_updates_database_and_affects_printed_receipt(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin);

        // Simpan konfigurasi baru via Livewire Form
        Livewire::test(ReceiptSettings::class)
            ->fillForm([
                'receipt_cafe_name'     => 'KAFE KOPI KDAI PREMIUM',
                'receipt_cafe_address'  => 'Ruko Grand Wisata Blok AA No. 15, Bekasi',
                'receipt_cafe_phone'    => '0811-2233-4455',
                'receipt_wifi_ssid'     => 'KDAI-Guest-HighSpeed',
                'receipt_wifi_password' => 'KopiMantap99',
                'receipt_feedback_info' => 'Instagram: @kopikdai.id',
                'receipt_footer_text'   => 'SENANG MELAYANI ANDA HARI INI!',
                'receipt_footer_subtext'=> '* Harap simpan struk ini untuk klaim poin *',
                'receipt_paper_width'   => '58mm',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Verifikasi database settings
        $this->assertEquals('KAFE KOPI KDAI PREMIUM', setting('receipt_cafe_name'));
        $this->assertEquals('KDAI-Guest-HighSpeed', setting('receipt_wifi_ssid'));

        // Buat dummy order dan cetak struk
        $category = Category::create([
            'name' => 'Signature Coffee',
            'description' => 'Coffee',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Aren Latte Signature',
            'sku' => Str::uuid()->toString(),
            'price' => 28000,
            'cost_price' => 12000,
            'stock_qty' => 50,
            'description' => 'Coffee',
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $admin->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Dimas Anggara',
            'status' => 'completed',
            'subtotal_order' => 28000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 28000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 28000,
            'discount_amount' => 0,
            'subtotal' => 28000,
        ]); 

        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_date' => now(),
            'amount_paid' => 30000,
            'change_return' => 2000,
            'payment_method' => 'cash',
            'status' => 'captured',
            'paid_at' => now(),
        ]);

        // Request halaman cetak struk via payment
        $response = $this->get(route('payments.print', ['payment' => $payment]));
        $response->assertStatus(200);

        // Verifikasi data dinamis tampil di struk
        $response->assertSee('KAFE KOPI KDAI PREMIUM');
        $response->assertSee('Ruko Grand Wisata Blok AA No. 15, Bekasi');
        $response->assertSee('0811-2233-4455');
        $response->assertSee('KDAI-Guest-HighSpeed');
        $response->assertSee('KopiMantap99');
        $response->assertSee('Instagram: @kopikdai.id');
        $response->assertSee('SENANG MELAYANI ANDA HARI INI!');
        $response->assertSee('* Harap simpan struk ini untuk klaim poin *');
    }

    public function test_empty_wifi_settings_are_omitted_cleanly_from_receipt(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        // Kosongkan wifi dan kritik saran
        SettingHelper::set('receipt_wifi_ssid', '');
        SettingHelper::set('receipt_wifi_password', '');
        SettingHelper::set('receipt_feedback_info', '');

        $order = Order::create([
            'user_id' => $admin->id,
            'order_type' => 'take_away',
            'customer_name' => 'Testing Guest',
            'status' => 'completed',
            'subtotal_order' => 15000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 15000,
        ]);

        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_date' => now(),
            'amount_paid' => 15000,
            'change_return' => 0,
            'payment_method' => 'cash',
            'status' => 'captured',
        ]);

        $response = $this->actingAs($admin)->get(route('payments.print', ['payment' => $payment]));
        $response->assertStatus(200);

        // Baris WiFi dan Kritik/Saran tidak boleh muncul jika nilainya kosong
        $response->assertDontSee('WiFi:');
        $response->assertDontSee('Kritik & Saran:');
    }
}
