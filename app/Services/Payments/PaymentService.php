<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\StockValidationException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\ShiftGuard;
use App\Services\StockService;
use App\Services\StockValidationService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected PaymentGatewayManager $gatewayManager)
    {
    }

    /**
     * Proses pembayaran baru untuk sebuah order.
     *
     * - Cash        → langsung captured
     * - QRIS/Ewallet/Transfer (mode manual) → pending, tunggu konfirmasi kasir
     * - QRIS/Ewallet/Transfer (mode gateway) → pending, tunggu webhook
     */
    public function process(Order $order, array $data, ?int $shiftId = null): Payment
    {
        $method  = $data['payment_method'];
        $channel = $data['payment_channel'] ?? null;
        $amount  = (float) $data['amount'];

        $this->validateOrder($order);
        $this->validateAmount($order, $method, $amount);

        return DB::transaction(function () use ($order, $method, $channel, $amount, $shiftId) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first() ?? $order;
            $this->validateOrder($lockedOrder);

            // Cek apakah sudah ada pembayaran pending untuk order ini (Idempotency Guard)
            $existingPending = $lockedOrder->payments()
                ->where('status', PaymentStatus::Pending->value)
                ->latest()
                ->first();

            if ($existingPending) {
                // Jika metode & channel pembayaran sama, kembalikan pembayaran pending yang sudah ada
                if ($existingPending->payment_method === $method && (string) $existingPending->payment_channel === (string) $channel) {
                    return $existingPending;
                }

                // Jika pelanggan berganti metode pembayaran, hapus pembayaran pending yang lama
                $existingPending->delete();
            }

            if ($method === 'cash') {
                return $this->processCash($lockedOrder, $amount, $shiftId);
            }

            return $this->processDigital($lockedOrder, $method, $channel, $amount, $shiftId);
        });
    }

    /**
     * Kasir konfirmasi pembayaran yang sedang menunggu (pending → captured).
     * Hanya berlaku di mode manual.
     */
    public function confirm(Payment $payment, User $confirmedBy): Payment
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw new \LogicException("Pembayaran tidak dalam status pending. Status saat ini: {$payment->status}");
        }

        // Guard: Pastikan kasir / toko memiliki shift aktif sebelum konfirmasi pembayaran
        $activeShift = ShiftGuard::getActiveShiftForConfirmation($confirmedBy);

        return DB::transaction(function () use ($payment, $confirmedBy, $activeShift) {
            $payment->update([
                'status'       => PaymentStatus::Captured->value,
                'paid_at'      => now(),
                'confirmed_by' => $confirmedBy->id,
                'confirmed_at' => now(),
                'shift_id'     => $payment->shift_id ?? $activeShift->id,
            ]);

            $order = $payment->order()->with('items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient')->first();
            $this->finalizeOrder($order, $payment->amount_paid);

            return $payment->fresh();
        });
    }

    /**
     * Tangani notifikasi webhook dari payment gateway.
     * Digunakan oleh PaymentWebhookController.
     *
     * Dilindungi oleh:
     * 1. DB::transaction untuk atomicity
     * 2. lockForUpdate untuk mencegah pemrosesan ganda
     * 3. processed_webhook_at sebagai idempotency guard
     */
    public function handleWebhook(array $payload): void
    {
        $result = $this->gatewayManager->handleWebhook($payload);

        DB::transaction(function () use ($result) {
            $payment = Payment::query()
                ->where('external_reference', $result['reference'])
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return; // Referensi tidak ditemukan, abaikan
            }

            // Sudah pernah diproses oleh webhook sebelumnya (idempotency guard)
            if ($payment->processed_webhook_at) {
                return;
            }

            // Sudah final, abaikan duplikasi
            if (in_array($payment->status, [PaymentStatus::Captured->value, PaymentStatus::Failed->value], true)) {
                return;
            }

            $newStatus = $result['status'];
            $payment->update([
                'status'               => $newStatus,
                'paid_at'              => $newStatus === PaymentStatus::Captured->value ? now() : $payment->paid_at,
                'processed_webhook_at' => now(),
            ]);

            if ($newStatus === PaymentStatus::Captured->value) {
                $order = $payment->order()
                    ->with('items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient')
                    ->first();
                $this->finalizeOrder($order, $payment->amount_paid);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    protected function processCash(Order $order, float $amount, ?int $shiftId): Payment
    {
        $due    = $this->getAmountDue($order);
        $change = max($amount - $due, 0);

        $payment = $order->payments()->create([
            'payment_method'  => 'cash',
            'payment_channel' => null,
            'provider'        => 'manual',
            'external_reference' => null,
            'status'          => PaymentStatus::Captured->value,
            'meta'            => null,
            'amount_paid'     => $amount,
            'change_return'   => $change,
            'payment_date'    => now(),
            'paid_at'         => now(),
            'shift_id'        => $shiftId,
        ]);

        $order->load('items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient');
        $this->finalizeOrder($order, $amount);

        return $payment;
    }

    protected function processDigital(Order $order, string $method, ?string $channel, float $amount, ?int $shiftId): Payment
    {
        $charge = $this->gatewayManager->createCharge($method, $order, $amount, $channel);

        $payment = $order->payments()->create([
            'payment_method'     => $method,
            'payment_channel'    => $channel,
            'provider'           => $charge['provider'],
            'external_reference' => $charge['reference'],
            'status'             => $charge['status'],
            'meta'               => $charge['payload'],
            'amount_paid'        => $amount,
            'change_return'      => 0,
            'payment_date'       => now(),
            'paid_at'            => $charge['status'] === PaymentStatus::Captured->value ? now() : null,
            'shift_id'           => $shiftId,
        ]);

        if ($charge['status'] === PaymentStatus::Captured->value) {
            $order->load('items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient');
            $this->finalizeOrder($order, $amount);
        }

        return $payment;
    }

    /**
     * Selesaikan order setelah pembayaran captured:
     * 1. Validasi kecukupan stok bahan baku
     * 2. Update status order
     * 3. Kurangi stok bahan baku (atomik, conditional)
     * 4. Tandai stok sudah dikurangi
     *
     * @throws StockValidationException jika stok tidak cukup
     */
    protected function finalizeOrder(Order $order, float $paidAmount): void
    {
        $captured   = (float) $order->payments()->where('status', PaymentStatus::Captured->value)->sum('amount_paid');
        $grandTotal = (float) $order->total_order;

        if (($grandTotal - $captured) <= 0 && $order->status !== OrderStatus::Completed->value) {

            // Validasi stok sebelum pengurangan
            StockValidationService::validateStockForOrder($order);

            $newStatus = \App\Support\Feature::enabled('kitchen_display')
                ? OrderStatus::Payment->value
                : OrderStatus::Completed->value;

            $order->update([
                'status'         => $newStatus,
                'stock_deducted' => true,
            ]);
            $order->logStatus(OrderStatus::from($newStatus), 'Pembayaran diterima oleh kasir.');

            StockService::reduceIngredientsFromOrder($order);
        }
    }

    protected function validateOrder(Order $order): void
    {
        if ($order->status === OrderStatus::Cancelled->value) {
            throw new \DomainException('Tidak bisa memproses pembayaran untuk order yang dibatalkan.');
        }

        // Hanya izinkan pembayaran untuk order yang sudah di-submit, draft (dengan item), atau open
        $allowedStatuses = [
            'open',
            OrderStatus::Draft->value,
            OrderStatus::Pending->value,
            OrderStatus::Submitted->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Payment->value,
        ];

        if (! in_array($order->status, $allowedStatuses, true)) {
            throw new \DomainException(
                'Order belum siap untuk dibayar. Status saat ini: ' . $order->status
                . '. Order harus di-submit terlebih dahulu.'
            );
        }

        if ($order->status === OrderStatus::Draft->value && $order->items()->count() === 0) {
            throw new \DomainException('Tidak bisa membayar order yang belum memiliki item.');
        }

        $due = $this->getAmountDue($order);
        if ($due <= 0) {
            throw new \DomainException('Order sudah lunas.');
        }
    }

    protected function validateAmount(Order $order, string $method, float $amount): void
    {
        $due = $this->getAmountDue($order);

        // Cash boleh lebih (ada kembalian), metode lain tidak
        if ($method !== 'cash' && $amount > $due) {
            throw new \DomainException('Jumlah pembayaran melebihi sisa tagihan.');
        }

        if ($method === 'cash' && $amount < $due) {
            throw new \DomainException('Uang tunai yang diterima kurang dari total tagihan (Rp ' . number_format($due, 0, ',', '.') . ').');
        }
    }

    protected function getAmountDue(Order $order): float
    {
        $captured   = (float) $order->payments()->where('status', PaymentStatus::Captured->value)->sum('amount_paid');
        $grandTotal = (float) ($order->total_order ?? 0);

        return max($grandTotal - $captured, 0);
    }
}
