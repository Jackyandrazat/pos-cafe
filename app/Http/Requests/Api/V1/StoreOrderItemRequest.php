<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'size_id' => ['nullable', 'exists:product_sizes,id'],
            'toppings' => ['sometimes', 'array'],
            'toppings.*.topping_id' => ['required_with:toppings', 'exists:toppings,id'],
            'toppings.*.quantity' => ['nullable', 'integer', 'min:1'],
            'toppings.*.name' => ['nullable', 'string', 'min:1'],
        ];
    }
}
