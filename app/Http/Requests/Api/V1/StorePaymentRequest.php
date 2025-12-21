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
            'payment_method' => ['required', 'in:cash,qris,transfer,ewallet'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
