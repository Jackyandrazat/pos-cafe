<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Topping;
use Livewire\Component;

class OrderItemBuilder extends Component
{
    public $products;
    public $toppings;
    public $selectedProductId;
    public $qty = 1;
    public $discount = 0;
    public $selectedToppingIds = [];
    public $selectedItems = [];
    public $orderId;

    public function mount($orderId = null)
    {
        $this->products = Product::all();
        $this->orderId = $orderId;
        $this->toppings = Topping::active()->orderBy('name')->get();

        if ($orderId) {
            $order = Order::with(['order_items.product', 'order_items.toppings'])->find($orderId);
            if ($order) {
                $this->selectedItems = $order->order_items->map(function ($item) {
                    $toppings = $item->toppings->map(function ($topping) use ($item) {
                        return [
                            'id' => $topping->topping_id,
                            'name' => $topping->name,
                            'price' => $topping->price,
                            'quantity' => $topping->quantity,
                            'total' => $topping->total,
                        ];
                    })->toArray();

                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'Produk Tidak Ditemukan',
                        'qty' => $item->qty ?? 1,
                        'price' => $item->price ?? 0,
                        'discount' => $item->discount_amount ?? 0,
                        'subtotal' => $item->subtotal ?? 0,
                        'toppings' => $toppings,
                        'toppings_total' => collect($toppings)->sum('total'),
                    ];
                })->toArray();

                $this->selectedItems = array_values($this->selectedItems);
                $this->updateSession();
            }
        } else {
            $this->selectedItems = session('selected_order_items', []);
            $this->selectedItems = array_values($this->selectedItems);
            $this->updateSession();
        }

    }

    public function addItem()
    {
        $product = Product::find($this->selectedProductId);
        if (!$product) {
            return;
        }

        $selectedToppings = Topping::whereIn('id', $this->selectedToppingIds ?? [])->get();
        $toppingsPayload = $selectedToppings->map(function ($topping) {
            return [
                'id' => $topping->id,
                'name' => $topping->name,
                'price' => $topping->price,
            ];
        })->toArray();

        $toppingsTotal = $selectedToppings->sum('price') * $this->qty;
        $baseSubtotal = ($product->price * $this->qty) + $toppingsTotal;
        $subtotal = $baseSubtotal - $this->discount;

        $quantity = $this->qty ?? 1;

        $this->selectedItems[] = [
            'product_id' => $product->id,
            'name' => $product->name ?? 'Produk Tidak Ditemukan',
            'price' => $product->price ?? 0,
            'qty' => $this->qty ?? 1,
            'discount' => $this->discount,
            'subtotal' => max($subtotal, 0),
            'toppings' => array_map(function ($topping) use ($quantity) {
                $price = $topping['price'];

                return [
                    'id' => $topping['id'],
                    'name' => $topping['name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                ];
            }, $toppingsPayload),
            'toppings_total' => $toppingsTotal,
        ];

        // reset
        $this->selectedProductId = null;
        $this->qty = 1;
        $this->discount = 0;
        $this->selectedToppingIds = [];

        $this->updateSession();
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems); // reset index
        $this->updateSession();
    }

    public function updateSession()
    {
        $this->selectedItems = array_values($this->selectedItems);
        session()->put('selected_order_items', $this->selectedItems);
    }


    public function render()
    {
        // return view('livewire.order-item-builder');
        return view('livewire.order-item-builder', [
        'totalSubtotal' => collect($this->selectedItems)->sum('subtotal'),
        ]);
    }
}
