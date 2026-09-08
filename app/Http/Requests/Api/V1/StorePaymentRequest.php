<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('method') && ! $this->has('payment_method')) {
            $this->merge(['payment_method' => $this->input('method')]);
        }

        if (! $this->filled('amount')) {
            $order = $this->route('order');
            if ($order instanceof \App\Models\Order) {
                $captured = (float) $order->payments()->where('status', 'captured')->sum('amount_paid');
                $due = max((float) ($order->total_order ?? 0) - $captured, 0);
                if ($due > 0) {
                    $this->merge(['amount' => $due]);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'payment_method'  => ['required', 'in:cash,qris,transfer,ewallet'],
            'payment_channel' => ['nullable', 'string', 'max:50'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Metode pembayaran tidak valid. Pilih: cash, qris, transfer, atau ewallet.',
            'amount.min'        => 'Jumlah pembayaran minimal Rp 1.',
        ];
    }
}
