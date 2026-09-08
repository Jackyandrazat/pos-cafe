<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePendingOrdersCommand extends Command
{
    protected $signature = 'orders:expire-pending {--minutes=15 : Batas menit sebelum order dianggap kadaluarsa}';

    protected $description = 'Batalkan pesanan pending atau draft yang tidak dibayar melebihi batas waktu';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $expiryTime = now()->subMinutes($minutes);

        $staleOrders = Order::whereIn('status', [OrderStatus::Draft->value, OrderStatus::Pending->value])
            ->where('created_at', '<', $expiryTime)
            ->get();

        $count = 0;

        foreach ($staleOrders as $order) {
            DB::transaction(function () use ($order) {
                // Hapus pending payments
                $order->payments()
                    ->where('status', PaymentStatus::Pending->value)
                    ->delete();

                // Kembalikan stok jika pernah dikurangi
                if ($order->stock_deducted) {
                    StockService::restoreIngredientsFromOrder($order);
                    $order->stock_deducted = false;
                }

                $order->status = OrderStatus::Cancelled->value;
                $order->save();
                $order->logStatus(OrderStatus::Cancelled, 'Auto-expired due to unpaid status');
            });

            $count++;
        }

        $this->info("Berhasil membatalkan {$count} pesanan kadaluarsa (> {$minutes} menit).");

        return self::SUCCESS;
    }
}
