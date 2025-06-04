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

    {{-- List Produk Dipesan --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach ($selectedItems as $index => $item)
            <div class="border p-3 rounded shadow text-sm bg-gray-50">
                <div class="font-bold">{{ $item['name'] }}</div>
                <div>Qty: {{ $item['qty'] }}</div>
                <div>Harga: Rp{{ number_format($item['price']) }}</div>
                <div>Diskon: Rp{{ number_format($item['discount']) }}</div>
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
