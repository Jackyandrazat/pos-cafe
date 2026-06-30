<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Models\Order;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PaymentResource;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Validasi awal + siapkan data shift sebelum create.
     * Tidak boleh buat record di sini — Filament masih akan memanggil handleRecordCreation.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user        = auth()->user();
        $activeShift = $user->activeShift();

        // Cek shift aktif
        if (! $activeShift) {
            Notification::make()
                ->danger()
                ->title('Shift belum dibuka')
                ->body('Kasir harus membuka shift sebelum membuat pembayaran.')
                ->send();

            $this->addError('order_id', 'Kasir harus membuka shift sebelum membuat pembayaran.');
            throw new Halt();
        }

        // Cek order valid
        $order = Order::find($data['order_id'] ?? null);

        if (! $order) {
            throw ValidationException::withMessages(['order_id' => 'Order tidak ditemukan.']);
        }

        if (in_array($order->status, ['completed', 'cancelled'])) {
            throw ValidationException::withMessages(['order_id' => 'Order sudah selesai atau dibatalkan.']);
        }

        // Sisipkan shift_id ke data agar bisa digunakan di handleRecordCreation
        $data['_shift_id'] = $activeShift->id;

        return $data;
    }

    /**
     * Delegasikan pembuatan record ke PaymentService.
     * Filament akan menggunakan record yang dikembalikan untuk redirect.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user     = auth()->user();
        $shiftId  = $data['_shift_id'] ?? null;
        $order    = Order::findOrFail($data['order_id']);

        try {
            $paymentData = [
                'payment_method'  => $data['payment_method'],
                'payment_channel' => $data['payment_channel'] ?? null,
                'amount'          => (float) $data['amount_paid'],
            ];

            $payment = app(PaymentService::class)->process($order, $paymentData, $shiftId);

            // Notifikasi database ke admin
            $admin = User::first();
            if ($admin) {
                Notification::make()
                    ->title('Pembayaran Diterima')
                    ->body("Rp " . number_format($payment->amount_paid, 0, ',', '.') . " via " . strtoupper($payment->payment_method) . " oleh {$user->name}.")
                    ->success()
                    ->sendToDatabase($admin);
            }

            return $payment;
        } catch (\DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            throw new Halt();
        }
    }

    /**
     * Setelah berhasil, redirect ke daftar pembayaran (bukan ke halaman edit).
     */
    protected function getRedirectUrl(): string
    {
        return PaymentResource::getUrl('index');
    }

    /**
     * Override notifikasi sukses dengan label Bahasa Indonesia.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil dibuat';
    }
}
