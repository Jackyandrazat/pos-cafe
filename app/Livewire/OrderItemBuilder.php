<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Topping;
use Livewire\Component;

class OrderItemBuilder extends Component
{
    public ?int $orderId = null;
    public array $selectedItems = [];

    // Filter & Search state
    public string $search = '';
    public ?int $selectedCategoryId = null;

    // Customization Modal State
    public bool $showCustomModal = false;
    public ?int $modalProductId = null;
    public ?string $modalProductName = null;
    public float $modalProductPrice = 0;
    public ?string $modalProductImage = null;
    public array $modalAvailableSizes = [];
    public array $modalAvailableToppings = [];

    public ?int $modalSelectedSizeId = null;
    public array $modalSelectedToppingIds = [];
    public int $modalQty = 1;
    public float $modalDiscount = 0;
    public float $modalItemSubtotal = 0;

    // Reset Confirm Modal State
    public bool $showResetConfirmModal = false;

    protected $listeners = ['resetOrderItems'];

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId;

        if ($orderId) {
            $order = Order::with(['order_items.product', 'order_items.toppings'])->find($orderId);
            if ($order) {
                $this->selectedItems = $order->order_items->map(function ($item) {
                    $toppings = $item->toppings->map(function ($topping) {
                        return [
                            'id' => $topping->topping_id,
                            'name' => $topping->name,
                            'price' => (float) $topping->price,
                            'quantity' => (int) $topping->quantity,
                            'total' => (float) $topping->total,
                        ];
                    })->toArray();

                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product?->name ?? 'Produk Tidak Ditemukan',
                        'qty' => (int) ($item->qty ?? 1),
                        'price' => (float) ($item->price ?? 0),
                        'discount' => (float) ($item->discount_amount ?? 0),
                        'subtotal' => (float) ($item->subtotal ?? 0),
                        'size_id' => $item->size_id ?? null,
                        'size_name' => $item->size?->name ?? null,
                        'toppings' => $toppings,
                        'toppings_total' => (float) collect($toppings)->sum('total'),
                    ];
                })->toArray();

                $this->selectedItems = array_values($this->selectedItems);
            }
        } else {
            $this->selectedItems = session('selected_order_items', []);
            $this->selectedItems = array_values($this->selectedItems);
        }
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->where('status_enabled', true)
            ->withCount(['products' => function ($q) {
                $q->where('status_enabled', true);
            }])
            ->orderBy('name')
            ->get();
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->where('status_enabled', true)
            ->with(['category', 'toppings' => function ($q) {
                $q->where('is_active', true);
            }, 'sizes', 'media', 'ingredients.ingredient'])
            ->when($this->selectedCategoryId, function ($q, $catId) {
                $q->where('category_id', $catId);
            })
            ->when(filled($this->search), function ($q) {
                $search = trim($this->search);
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function filterCategory(?int $categoryId = null): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * Klik kartu produk dari katalog
     */
    public function clickProduct(int $productId): void
    {
        $product = Product::with(['toppings' => function ($q) {
            $q->where('is_active', true);
        }, 'sizes', 'media', 'ingredients.ingredient'])->find($productId);

        if (! $product) {
            return;
        }

        // Cek jika produk punya topping atau size -> Buka modal kustomisasi
        $hasToppings = $product->toppings->isNotEmpty();
        $hasSizes = $product->sizes->isNotEmpty();

        if ($hasToppings || $hasSizes) {
            $this->openCustomModal($product);
        } else {
            // Quick add langsung ke keranjang jika tanpa opsi
            $this->quickAddProduct($product);
        }
    }

    protected function quickAddProduct(Product $product): void
    {
        $unitPrice = (float) $product->price;

        // Cek apakah item yang sama (tanpa size & tanpa topping) sudah ada di keranjang
        $existingIndex = null;
        foreach ($this->selectedItems as $index => $item) {
            if ($item['product_id'] === $product->id && empty($item['toppings']) && empty($item['size_id'])) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Tambah quantity saja
            $this->incrementQty($existingIndex);
            return;
        }

        $this->selectedItems[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $unitPrice,
            'qty' => 1,
            'discount' => 0,
            'subtotal' => $unitPrice,
            'size_id' => null,
            'size_name' => null,
            'toppings' => [],
            'toppings_total' => 0,
        ];

        $this->updateSession();
    }

    public function openCustomModal(Product $product): void
    {
        $this->modalProductId = $product->id;
        $this->modalProductName = $product->name;
        $this->modalProductPrice = (float) $product->price;
        $this->modalProductImage = $product->getFirstMediaUrl('product', 'thumb') ?: $product->getFirstMediaUrl('product') ?: null;

        $this->modalAvailableSizes = $product->sizes->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'price_modifier' => (float) $s->price_modifier,
            ];
        })->toArray();

        $this->modalAvailableToppings = $product->toppings->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'price' => (float) $t->price,
            ];
        })->toArray();

        // Default size pertama jika ada
        $this->modalSelectedSizeId = ! empty($this->modalAvailableSizes) ? $this->modalAvailableSizes[0]['id'] : null;
        $this->modalSelectedToppingIds = [];
        $this->modalQty = 1;
        $this->modalDiscount = 0;

        $this->recalculateModalSubtotal();
        $this->showCustomModal = true;
    }

    public function closeCustomModal(): void
    {
        $this->showCustomModal = false;
        $this->modalProductId = null;
    }

    public function selectModalSize(?int $sizeId): void
    {
        $this->modalSelectedSizeId = $sizeId !== null ? (int) $sizeId : null;
        $this->recalculateModalSubtotal();
    }

    public function toggleModalTopping(int $toppingId): void
    {
        $toppingId = (int) $toppingId;
        $currentIds = array_map('intval', $this->modalSelectedToppingIds);

        if (in_array($toppingId, $currentIds, true)) {
            $this->modalSelectedToppingIds = array_values(array_filter(
                $currentIds,
                fn ($id) => $id !== $toppingId
            ));
        } else {
            $currentIds[] = $toppingId;
            $this->modalSelectedToppingIds = array_values($currentIds);
        }

        $this->recalculateModalSubtotal();
    }

    public function incrementModalQty(): void
    {
        $this->modalQty++;
        $this->recalculateModalSubtotal();
    }

    public function decrementModalQty(): void
    {
        if ($this->modalQty > 1) {
            $this->modalQty--;
            $this->recalculateModalSubtotal();
        }
    }

    public function updatedModalDiscount(): void
    {
        $this->modalDiscount = max((float) $this->modalDiscount, 0);
        $this->recalculateModalSubtotal();
    }

    public function recalculateModalSubtotal(): void
    {
        $basePrice = $this->modalProductPrice;

        // Size modifier
        if ($this->modalSelectedSizeId && ! empty($this->modalAvailableSizes)) {
            foreach ($this->modalAvailableSizes as $size) {
                if ($size['id'] === $this->modalSelectedSizeId) {
                    $basePrice += $size['price_modifier'];
                    break;
                }
            }
        }

        // Toppings sum
        $toppingsPerUnit = 0;
        if (! empty($this->modalSelectedToppingIds)) {
            foreach ($this->modalAvailableToppings as $top) {
                if (in_array($top['id'], $this->modalSelectedToppingIds, true)) {
                    $toppingsPerUnit += $top['price'];
                }
            }
        }

        $totalPerItem = ($basePrice + $toppingsPerUnit) * $this->modalQty;
        $this->modalItemSubtotal = max($totalPerItem - (float) $this->modalDiscount, 0);
    }

    public function addCustomizedItem(): void
    {
        $basePrice = $this->modalProductPrice;
        $sizeName = null;

        if ($this->modalSelectedSizeId && ! empty($this->modalAvailableSizes)) {
            foreach ($this->modalAvailableSizes as $size) {
                if ($size['id'] === $this->modalSelectedSizeId) {
                    $basePrice += $size['price_modifier'];
                    $sizeName = $size['name'];
                    break;
                }
            }
        }

        $chosenToppings = [];
        $toppingsTotal = 0;
        foreach ($this->modalAvailableToppings as $top) {
            if (in_array($top['id'], $this->modalSelectedToppingIds, true)) {
                $topTotal = $top['price'] * $this->modalQty;
                $toppingsTotal += $topTotal;
                $chosenToppings[] = [
                    'id' => $top['id'],
                    'name' => $top['name'],
                    'price' => $top['price'],
                    'quantity' => $this->modalQty,
                    'total' => $topTotal,
                ];
            }
        }

        $subtotal = (($basePrice * $this->modalQty) + $toppingsTotal) - $this->modalDiscount;

        $displayName = $this->modalProductName;
        if ($sizeName) {
            $displayName .= " ({$sizeName})";
        }

        $this->selectedItems[] = [
            'product_id' => $this->modalProductId,
            'name' => $displayName,
            'price' => $basePrice,
            'qty' => $this->modalQty,
            'discount' => $this->modalDiscount,
            'subtotal' => max($subtotal, 0),
            'size_id' => $this->modalSelectedSizeId,
            'size_name' => $sizeName,
            'toppings' => $chosenToppings,
            'toppings_total' => $toppingsTotal,
        ];

        $this->closeCustomModal();
        $this->updateSession();
    }

    public function incrementQty(int $index): void
    {
        if (! isset($this->selectedItems[$index])) {
            return;
        }

        $this->selectedItems[$index]['qty']++;
        $qty = $this->selectedItems[$index]['qty'];
        $unitPrice = (float) $this->selectedItems[$index]['price'];
        $discount = (float) ($this->selectedItems[$index]['discount'] ?? 0);

        // Update topping totals
        $toppingsTotal = 0;
        if (! empty($this->selectedItems[$index]['toppings'])) {
            foreach ($this->selectedItems[$index]['toppings'] as &$top) {
                $top['quantity'] = $qty;
                $top['total'] = $top['price'] * $qty;
                $toppingsTotal += $top['total'];
            }
            unset($top);
        }

        $this->selectedItems[$index]['toppings_total'] = $toppingsTotal;
        $this->selectedItems[$index]['subtotal'] = max((($unitPrice * $qty) + $toppingsTotal) - $discount, 0);

        $this->updateSession();
    }

    public function decrementQty(int $index): void
    {
        if (! isset($this->selectedItems[$index])) {
            return;
        }

        if ($this->selectedItems[$index]['qty'] > 1) {
            $this->selectedItems[$index]['qty']--;
            $qty = $this->selectedItems[$index]['qty'];
            $unitPrice = (float) $this->selectedItems[$index]['price'];
            $discount = (float) ($this->selectedItems[$index]['discount'] ?? 0);

            $toppingsTotal = 0;
            if (! empty($this->selectedItems[$index]['toppings'])) {
                foreach ($this->selectedItems[$index]['toppings'] as &$top) {
                    $top['quantity'] = $qty;
                    $top['total'] = $top['price'] * $qty;
                    $toppingsTotal += $top['total'];
                }
                unset($top);
            }

            $this->selectedItems[$index]['toppings_total'] = $toppingsTotal;
            $this->selectedItems[$index]['subtotal'] = max((($unitPrice * $qty) + $toppingsTotal) - $discount, 0);

            $this->updateSession();
        } else {
            $this->removeItem($index);
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
        $this->updateSession();
    }

    public function promptResetCart(): void
    {
        $this->showResetConfirmModal = true;
    }

    public function cancelResetCart(): void
    {
        $this->showResetConfirmModal = false;
    }

    public function confirmResetCart(): void
    {
        $this->selectedItems = [];
        $this->updateSession();
        $this->showResetConfirmModal = false;
    }

    public function clearAllItems(): void
    {
        $this->confirmResetCart();
    }

    public function updateSession(): void
    {
        $this->selectedItems = array_values($this->selectedItems);
        session()->put('selected_order_items', $this->selectedItems);
    }

    public function resetOrderItems(): void
    {
        if (! $this->orderId) {
            $this->selectedItems = [];
            $this->updateSession();
        }
    }

    public function render()
    {
        return view('livewire.order-item-builder', [
            'categories' => $this->categories,
            'products' => $this->products,
            'totalSubtotal' => collect($this->selectedItems)->sum('subtotal'),
            'totalItemsCount' => collect($this->selectedItems)->sum('qty'),
        ]);
    }
}
