<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use Livewire\Component;

class OrderItemBuilder extends Component
{
    public $products;
    public $selectedProductId;
    public $qty = 1;
    public $discount = 0;
    public $selectedItems = [];
    public $orderId;

    public function mount($orderId = null)
    {
        $this->products = Product::all();
        $this->orderId = $orderId;

        if ($orderId) {
            $order = Order::with('order_items.product')->find($orderId);
            if ($order) {
                $this->selectedItems = $order->order_items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'Produk Tidak Ditemukan',
                        'qty' => $item->qty ?? 1,
                        'price' => $item->price ?? 0,
                        'discount' => $item->discount ?? 0,
                        'subtotal' => $item->subtotal ?? 0,
                    ];
                })->toArray();
            }
        }

    }

    public function addItem()
    {
        $product = Product::find($this->selectedProductId);
        if (!$product) {
            return;
        }

        $subtotal = ($product->price * $this->qty) - $this->discount;

        $this->selectedItems[] = [
            'product_id' => $product->id,
            'name' => $product->name ?? 'Produk Tidak Ditemukan',
            'price' => $product->price ?? 0,
            'qty' => $this->qty ?? 1,
            'discount' => $this->discount,
            'subtotal' => max($subtotal, 0),
        ];

        // reset
        $this->selectedProductId = null;
        $this->qty = 1;
        $this->discount = 0;

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
