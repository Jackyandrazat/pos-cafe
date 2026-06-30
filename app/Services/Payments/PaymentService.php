<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\StockService;
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
            if ($method === 'cash') {
                return $this->processCash($order, $amount, $shiftId);
            }

            return $this->processDigital($order, $method, $channel, $amount, $shiftId);
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

        return DB::transaction(function () use ($payment, $confirmedBy) {
            $payment->update([
                'status'       => PaymentStatus::Captured->value,
                'paid_at'      => now(),
                'confirmed_by' => $confirmedBy->id,
                'confirmed_at' => now(),
            ]);

            $order = $payment->order()->with('order_items.product.ingredients.ingredient')->first();
            $this->finalizeOrder($order, $payment->amount_paid);

            return $payment->fresh();
        });
    }

    /**
     * Tangani notifikasi webhook dari payment gateway.
     * Digunakan oleh PaymentWebhookController.
     */
    public function handleWebhook(array $payload): void
    {
        $result = $this->gatewayManager->handleWebhook($payload);

        $payment = Payment::where('external_reference', $result['reference'])->first();

        if (! $payment) {
            return; // Referensi tidak ditemukan, abaikan
        }

        if (in_array($payment->status, [PaymentStatus::Captured->value, PaymentStatus::Failed->value], true)) {
            return; // Sudah final, abaikan duplikasi
        }

        $newStatus = $result['status'];
        $payment->update([
            'status'  => $newStatus,
            'paid_at' => $newStatus === PaymentStatus::Captured->value ? now() : $payment->paid_at,
        ]);

        if ($newStatus === PaymentStatus::Captured->value) {
            $order = $payment->order()->with('order_items.product.ingredients.ingredient')->first();
            $this->finalizeOrder($order, $payment->amount_paid);
        }
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
            $order->load('order_items.product.ingredients.ingredient');
            $this->finalizeOrder($order, $amount);
        }

        return $payment;
    }

    /**
     * Selesaikan order setelah pembayaran captured:
     * update status order + kurangi stok bahan baku.
     */
    protected function finalizeOrder(Order $order, float $paidAmount): void
    {
        $captured   = (float) $order->payments()->where('status', PaymentStatus::Captured->value)->sum('amount_paid');
        $grandTotal = (float) $order->total_order;

        if (($grandTotal - $captured) <= 0 && $order->status !== OrderStatus::Completed->value) {
            $order->update(['status' => OrderStatus::Payment->value]);
            $order->logStatus(OrderStatus::Payment, 'Pembayaran diterima oleh kasir.');
            StockService::reduceIngredientsFromOrder($order);
        }
    }

    protected function validateOrder(Order $order): void
    {
        if ($order->status === OrderStatus::Cancelled->value) {
            throw new \DomainException('Tidak bisa memproses pembayaran untuk order yang dibatalkan.');
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
    }

    protected function getAmountDue(Order $order): float
    {
        $captured   = (float) $order->payments()->where('status', PaymentStatus::Captured->value)->sum('amount_paid');
        $grandTotal = (float) ($order->total_order ?? 0);

        return max($grandTotal - $captured, 0);
    }
}
