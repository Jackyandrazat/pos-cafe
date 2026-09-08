<?php

namespace Tests\Feature\Filament;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentConfirmationShiftGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_confirm_payment_in_filament_without_active_shift(): void
    {
        $role = Role::create(['name' => 'kasir']);
        $kasir = User::factory()->create();
        $kasir->roles()->attach($role);

        $customer = User::factory()->create(['is_guest' => true]);
        $order = Order::factory()->create(['user_id' => $customer->id, 'total_order' => 50000]);

        // Pembayaran pending dari self-order tanpa shift
        $payment = Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'qris',
            'provider'       => 'qris-manual',
            'status'         => PaymentStatus::Pending->value,
            'amount_paid'    => 50000,
            'payment_date'   => now(),
            'shift_id'       => null,
        ]);

        $this->actingAs($kasir);

        // Kasir belum membuka shift, panggil action confirm di tabel Filament
        Livewire::test(ListPayments::class)
            ->callTableAction('confirm', $payment);

        // Status harus tetap pending dan shift_id tetap null
        $payment->refresh();
        $this->assertEquals(PaymentStatus::Pending->value, (string) $payment->status);
        $this->assertNull($payment->shift_id);
        $this->assertNull($payment->confirmed_by);
    }

    public function test_cashier_can_confirm_payment_in_filament_when_shift_is_active(): void
    {
        $role = Role::create(['name' => 'kasir']);
        $kasir = User::factory()->create();
        $kasir->roles()->attach($role);

        // Buka shift kasir
        $shift = Shift::create([
            'user_id'         => $kasir->id,
            'shift_open_time' => now(),
            'opening_balance' => 100000,
        ]);

        $customer = User::factory()->create(['is_guest' => true]);
        $order = Order::factory()->create(['user_id' => $customer->id, 'total_order' => 50000]);

        // Pembayaran pending dari self-order
        $payment = Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'qris',
            'provider'       => 'qris-manual',
            'status'         => PaymentStatus::Pending->value,
            'amount_paid'    => 50000,
            'payment_date'   => now(),
            'shift_id'       => null,
        ]);

        $this->actingAs($kasir);

        // Kasir mengonfirmasi pembayaran di tabel Filament
        Livewire::test(ListPayments::class)
            ->callTableAction('confirm', $payment);

        // Status harus berubah menjadi captured dan shift_id terisi dengan shift kasir
        $payment->refresh();
        $this->assertEquals(PaymentStatus::Captured->value, (string) $payment->status);
        $this->assertEquals($shift->id, $payment->shift_id);
        $this->assertEquals($kasir->id, $payment->confirmed_by);
    }
}
