<div class="p-6 space-y-4 border rounded-md bg-white shadow">
    {{-- Form Tambah Produk --}}
    <div class="grid grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Produk</label>
            <select wire:model="selectedProductId"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Qty</label>
            <input type="number" wire:model="qty" min="1"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Diskon</label>
            <input type="number" wire:model="discount" min="0"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
        </div>

        <div class="pt-6">
            <button type="button" wire:click="addItem"
                    class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                Tambah Produk
            </button>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Topping (opsional)</label>
        @if ($toppings->isEmpty())
            <p class="text-sm text-gray-500 border border-dashed border-gray-300 rounded-md p-3">
                Belum ada topping tersedia. Tambahkan topping terlebih dahulu melalui backend.
            </p>
        @else
            <div class="border border-gray-200 rounded-md p-3 max-h-40 overflow-y-auto flex flex-wrap gap-3 text-sm">
                @foreach ($toppings as $topping)
                    <label class="inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full px-3 py-1">
                        <input type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                               wire:model="selectedToppingIds" value="{{ $topping->id }}">
                        <span class="text-gray-700">{{ $topping->name }} (Rp{{ number_format($topping->price) }})</span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    {{-- List Produk Dipesan --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach ($selectedItems as $index => $item)
            <div class="border p-3 rounded shadow text-sm bg-gray-50">
                <div class="font-bold">{{ $item['name'] }}</div>
                <div>Qty: {{ $item['qty'] }}</div>
                <div>Harga: Rp{{ number_format($item['price']) }}</div>
                <div>Diskon: Rp{{ number_format($item['discount']) }}</div>
                @if (!empty($item['toppings']))
                    <div class="mt-2">
                        <div class="text-xs font-semibold text-gray-600">Topping:</div>
                        <ul class="mt-1 text-xs text-gray-600 list-disc pl-4 space-y-0.5">
                            @foreach ($item['toppings'] as $topping)
                                <li>
                                    {{ $topping['name'] }} (Rp{{ number_format($topping['price']) }} x {{ $topping['quantity'] }})
                                </li>
                            @endforeach
                        </ul>
                        <div class="text-sm font-semibold text-gray-700 mt-2">
                            Total Topping: Rp{{ number_format($item['toppings_total'] ?? 0) }}
                        </div>
                    </div>
                @endif
                <div class="font-semibold">Subtotal: Rp{{ number_format($item['subtotal']) }}</div>
                <button type="button" wire:click="removeItem({{ $index }})"
                        class="text-red-600 text-xs mt-2 underline">Hapus</button>
            </div>
        @endforeach
    </div>

    {{-- Total --}}
    <div class="text-right font-bold text-lg text-gray-800">
        Subtotal: Rp{{ number_format($totalSubtotal ?? 0) }}
    </div>
</div>
