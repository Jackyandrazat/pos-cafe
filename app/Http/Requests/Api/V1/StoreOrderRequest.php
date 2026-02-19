<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:dine_in,take_away,delivery'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'discount_order' => ['nullable', 'numeric', 'min:0'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
            'gift_card_code' => ['nullable', 'string', 'max:50'],
            'gift_card_amount' => ['nullable', 'numeric', 'min:0.01', 'required_with:gift_card_code'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.toppings' => ['sometimes', 'array'],
            'items.*.toppings.*.topping_id' => ['required_with:items.*.toppings', 'exists:toppings,id'],
            'items.*.toppings.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.toppings.*.name' => ['nullable', 'string', 'min:1'],
            'items.*.toppings.*.price' => ['nullable', 'integer', 'min:1'],
            'items.*.size.size_id' => ['nullable', 'exists:product_sizes,id'],

        ];
    }
}
