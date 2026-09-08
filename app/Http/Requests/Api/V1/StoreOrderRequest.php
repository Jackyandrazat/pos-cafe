<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('order_type') && $this->input('order_type') === 'takeaway') {
            $this->merge(['order_type' => 'take_away']);
        }

        if ($this->has('promo_code') && ! $this->has('promotion_code')) {
            $this->merge(['promotion_code' => $this->input('promo_code')]);
        }

        if ($this->filled('table_id')) {
            $rawTable = $this->input('table_id');
            $tableExists = \App\Models\CafeTable::where('id', $rawTable)->exists();
            if (! $tableExists) {
                $foundByNumber = \App\Models\CafeTable::where('table_number', (string) $rawTable)->first();
                if ($foundByNumber) {
                    $this->merge(['table_id' => $foundByNumber->id]);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:dine_in,take_away,takeaway,delivery'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'discount_order' => ['nullable', 'numeric', 'min:0'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'gift_card_code' => ['nullable', 'string', 'max:50'],
            'gift_card_amount' => ['nullable', 'numeric', 'min:0.01'],
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
