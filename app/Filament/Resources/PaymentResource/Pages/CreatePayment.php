<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Models\Shift;
use Filament\Actions;
use App\Models\Payment;
use App\Services\StockService;
use Filament\Notifications\Notification;
use App\Notifications\PaymentNotification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PaymentResource;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['payment_date'] = now();
        $user = auth()->user();
        $activeShift = $user->activeShift();

        if (! $activeShift) {
            Notification::make()
                ->danger()
                ->title('Shift belum dibuka')
                ->body('Kasir harus membuka shift sebelum membuat pembayaran.')
                ->send();

            $this->addError('order_id', 'Kasir harus membuka shift sebelum membuat pembayaran.');

            throw new Halt();
        }

        $data['shift_id'] = $activeShift->id;
        $orderForPaymentExist = \App\Models\Order::find($data['order_id'] ?? null);

        if ($orderForPaymentExist && $orderForPaymentExist->status === 'completed') {
            throw ValidationException::withMessages([
                'order_id' => 'Order sudah selesai, tidak bisa dibuat pembayaran.',
            ]);
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $payment = $this->record;
        // $order = $payment->order;
        $order = $payment->order()->with('order_items.product.ingredients.ingredient')->first();
        // dd($order);

        $kasir = auth()->user(); // kasir yang login
        $payment->change_return = max(($payment->amount_paid ?? 0) - ($order->total_order ?? 0), 0);
        $totalBayar = $payment->amount_paid;
        $payment->save();
        $admin = \App\Models\User::first(); // sementara ambil user pertama

        if ($admin) {
            \Filament\Notifications\Notification::make()
                ->title('Pembayaran Berhasil')
                ->body("Pembayaran sebesar Rp " . number_format($totalBayar, 0, ',', '.') . " berhasil dilakukan oleh kasir {$kasir->name}.")
                ->success()
                ->sendToDatabase($admin);
        }
        // $recipient = auth()->user();
        // $adminUser = \App\Models\User::find(1);

        // if ($recipient) {
        //     $recipient->notify(new PaymentNotification($payment));
        // }
        // if ($adminUser) {
        //     $adminUser->notify(new PaymentNotification($payment));
        // }

        if ($order) {
            $order->update([
                'status' => 'completed',
            ]);
        }
        StockService::reduceIngredientsFromOrder($order);
    }
}
