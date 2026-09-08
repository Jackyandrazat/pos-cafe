<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Models\Order;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PaymentResource;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function mount(): void
    {
        parent::mount();

        $orderId = request()->query('order_id');
        if ($orderId && $order = Order::find($orderId)) {
            $this->form->fill([
                'order_id'       => (int) $order->id,
                'amount_paid'    => (float) $order->total_order,
                'payment_method' => 'cash',
            ]);
        }
    }

    /**
     * Validasi awal + siapkan data shift sebelum create.
     * Tidak boleh buat record di sini — Filament masih akan memanggil handleRecordCreation.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user        = auth()->user();
        try {
            $activeShift = \App\Services\ShiftGuard::getActiveShiftForConfirmation($user);
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Shift belum dibuka')
                ->body($e->getMessage())
                ->send();

            $this->addError('order_id', $e->getMessage());
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
     * Override notifikasi sukses dengan aksi cetak struk thermal instan.
     */
    protected function getCreatedNotification(): ?Notification
    {
        $change = $this->record->change_return;
        $body   = 'Nominal Rp ' . number_format($this->record->amount_paid, 0, ',', '.') . ' (' . strtoupper($this->record->payment_method) . ').';
        if ($this->record->payment_method === 'cash') {
            $body .= ' Kembalian: Rp ' . number_format($change, 0, ',', '.');
        }

        return Notification::make()
            ->success()
            ->title('Pembayaran Berhasil')
            ->body($body)
            ->actions([
                Action::make('print_receipt')
                    ->label('🖨️ Cetak Struk')
                    ->button()
                    ->color('success')
                    ->url(route('payments.print', ['payment' => $this->record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
