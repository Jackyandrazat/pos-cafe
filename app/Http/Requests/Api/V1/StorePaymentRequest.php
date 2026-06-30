<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
