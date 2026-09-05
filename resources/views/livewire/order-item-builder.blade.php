<div class="mt-4 space-y-6">
    {{-- Layout Utama Split POS: Katalog di Kiri (65%), Keranjang di Kanan (35%) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- ========================================================================= --}}
        {{-- KOLOM KIRI: KATALOG PRODUK POS (lg:col-span-8)                             --}}
        {{-- ========================================================================= --}}
        <div class="lg:col-span-8 space-y-4">
            
            {{-- Header Katalog & Search Bar --}}
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Katalog Menu
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih atau klik menu untuk menambahkan pesanan ke keranjang.</p>
                    </div>

                    {{-- Input Pencarian --}}
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari menu / SKU..."
                            class="w-full pl-9 pr-8 py-2 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                        />
                        @if ($search)
                            <button
                                type="button"
                                wire:click="clearSearch"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Kategori (Horizontal Pills) --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-thin">
                    <button
                        type="button"
                        wire:click="filterCategory(null)"
                        @class([
                            'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 shadow-sm',
                            'bg-primary-600 text-white shadow-primary-500/20' => is_null($selectedCategoryId),
                            'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' => !is_null($selectedCategoryId),
                        ])
                    >
                        <span>Semua</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ is_null($selectedCategoryId) ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                            {{ $products->count() }}
                        </span>
                    </button>

                    @foreach ($categories as $cat)
                        <button
                            type="button"
                            wire:click="filterCategory({{ $cat->id }})"
                            @class([
                                'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 shadow-sm',
                                'bg-primary-600 text-white shadow-primary-500/20' => $selectedCategoryId === $cat->id,
                                'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' => $selectedCategoryId !== $cat->id,
                            ])
                        >
                            <span>{{ $cat->name }}</span>
                            @if ($cat->products_count > 0)
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategoryId === $cat->id ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                                    {{ $cat->products_count }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid Kartu Produk POS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @forelse ($products as $product)
                    @php
                        $inStock = $product->hasSufficientStock(1);
                        $thumbUrl = $product->getFirstMediaUrl('product', 'thumb') ?: $product->getFirstMediaUrl('product') ?: null;
                        
                        // Cek berapa banyak qty produk ini sudah ada di keranjang
                        $inCartCount = collect($selectedItems)->where('product_id', $product->id)->sum('qty');
                        $hasOptions = $product->toppings->isNotEmpty() || $product->sizes->isNotEmpty();
                    @endphp

                    <div
                        wire:key="product-{{ $product->id }}"
                        @if ($inStock)
                            wire:click="clickProduct({{ $product->id }})"
                        @endif
                        @class([
                            'group relative flex flex-col justify-between bg-white dark:bg-gray-800 rounded-xl border transition-all select-none',
                            'cursor-pointer hover:shadow-md hover:border-primary-500/50 dark:hover:border-primary-500/50 hover:-translate-y-0.5 border-gray-200 dark:border-gray-700' => $inStock,
                            'opacity-50 cursor-not-allowed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' => !$inStock,
                        ])
                    >
                        {{-- Badge Qty In-Cart --}}
                        @if ($inCartCount > 0)
                            <div class="absolute -top-2 -right-2 z-10 bg-primary-600 text-white font-extrabold text-[11px] px-2 py-0.5 rounded-full shadow-md animate-pulse">
                                {{ $inCartCount }}x
                            </div>
                        @endif

                        {{-- Image / Visual Header --}}
                        <div class="relative w-full aspect-[4/3] rounded-t-xl overflow-hidden bg-gray-100 dark:bg-gray-700/50">
                            @if ($thumbUrl)
                                <img
                                    src="{{ $thumbUrl }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-amber-500/10 via-primary-500/10 to-orange-500/10 text-primary-600 dark:text-primary-400">
                                    <svg class="w-9 h-9 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span class="text-[11px] font-bold tracking-wider uppercase mt-1 opacity-75">
                                        {{ Str::limit($product->category?->name ?? 'Menu', 10) }}
                                    </span>
                                </div>
                            @endif

                            {{-- Badges di Atas Gambar --}}
                            @if (! $inStock)
                                <div class="absolute inset-0 bg-black/60 backdrop-blur-[1px] flex items-center justify-center">
                                    <span class="bg-red-600 text-white font-bold text-xs uppercase tracking-wider px-2.5 py-1 rounded-md shadow">
                                        Habis
                                    </span>
                                </div>
                            @elseif ($hasOptions)
                                <div class="absolute bottom-2 left-2">
                                    <span class="bg-black/60 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-0.5 rounded shadow">
                                        Opsi +
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Card Body --}}
                        <div class="p-3 flex flex-col justify-between flex-grow space-y-2">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white line-clamp-2 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $product->name }}
                                </h4>
                                @if ($product->category)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-700/50">
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                                    Rp{{ number_format($product->price, 0, ',', '.') }}
                                </span>

                                @if ($inStock)
                                    <button
                                        type="button"
                                        class="w-6 h-6 rounded-md bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 group-hover:bg-primary-600 group-hover:text-white flex items-center justify-center transition"
                                        title="Tambah"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak ada produk ditemukan</p>
                        <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci lain atau pilih kategori berbeda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KOLOM KANAN: PANEL KERANJANG & RINGKASAN POS (lg:col-span-4)               --}}
        {{-- ========================================================================= --}}
        <div class="lg:col-span-4 sticky top-6 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                
                {{-- Header Keranjang --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Keranjang Pesanan</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $totalItemsCount }} Item Dipilih</p>
                        </div>
                    </div>

                    @if (! empty($selectedItems))
                        <button
                            type="button"
                            wire:click="clearAllItems"
                            wire:confirm="Yakin ingin mengosongkan seluruh item di keranjang?"
                            class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 flex items-center gap-1 transition"
                            title="Kosongkan Keranjang"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Reset</span>
                        </button>
                    @endif
                </div>

                {{-- Daftar Item Pesanan --}}
                <div class="p-3 divide-y divide-gray-100 dark:divide-gray-700/60 max-h-[460px] overflow-y-auto scrollbar-thin">
                    @forelse ($selectedItems as $index => $item)
                        <div class="py-3 first:pt-1 last:pb-1 space-y-1.5" wire:key="cart-item-{{ $index }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-grow">
                                    <h5 class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                                        {{ $item['name'] }}
                                    </h5>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Rp{{ number_format($item['price'], 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Subtotal Item --}}
                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                                        Rp{{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Rincian Topping --}}
                            @if (! empty($item['toppings']))
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($item['toppings'] as $topping)
                                        <span class="inline-flex items-center text-[10px] bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 rounded px-1.5 py-0.5">
                                            + {{ $topping['name'] }} (Rp{{ number_format($topping['price'], 0, ',', '.') }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Diskon Per Item --}}
                            @if (! empty($item['discount']) && $item['discount'] > 0)
                                <div class="text-[10px] text-red-500">
                                    Diskon: -Rp{{ number_format($item['discount'], 0, ',', '.') }}
                                </div>
                            @endif

                            {{-- Action Stepper & Delete --}}
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/60 rounded-lg p-0.5 border border-gray-200 dark:border-gray-600">
                                    <button
                                        type="button"
                                        wire:click="decrementQty({{ $index }})"
                                        class="w-6 h-6 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-xs font-bold shadow-xs transition"
                                    >
                                        -
                                    </button>
                                    <span class="w-8 text-center text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $item['qty'] }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="incrementQty({{ $index }})"
                                        class="w-6 h-6 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-xs font-bold shadow-xs transition"
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 p-1 transition"
                                    title="Hapus menu"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center space-y-2">
                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mx-auto flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-gray-400 px-4">Pilih menu dari katalog di sebelah kiri untuk memasukkan pesanan.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer Ringkasan Biaya --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-200 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Total Kuantitas</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $totalItemsCount }} item</span>
                    </div>

                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600 flex items-baseline justify-between">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Subtotal Order</span>
                        <span class="text-lg font-black text-primary-600 dark:text-primary-400">
                            Rp{{ number_format($totalSubtotal ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="text-[10px] text-gray-400 dark:text-gray-500 italic text-center">
                        * Diskon promo & gift card dihitung otomatis pada form utama di atas.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KUSTOMISASI PRODUK (Ukuran, Topping, Kuantitas)                      --}}
    {{-- ========================================================================= --}}
    @if ($showCustomModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150"
                wire:click.outside="closeCustomModal"
            >
                {{-- Header Modal --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if ($modalProductImage)
                            <img src="{{ $modalProductImage }}" class="w-12 h-12 object-cover rounded-lg shadow-xs" alt="{{ $modalProductName }}">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-base">
                                {{ substr($modalProductName, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $modalProductName }}
                            </h3>
                            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                                Dasar: Rp{{ number_format($modalProductPrice, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeCustomModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto scrollbar-thin">
                    {{-- 1. Pilihan Ukuran / Size (jika ada) --}}
                    @if (! empty($modalAvailableSizes))
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                                Pilih Ukuran (Size)
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach ($modalAvailableSizes as $size)
                                    @php
                                        $isSelected = $modalSelectedSizeId === $size['id'];
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectModalSize({{ $size['id'] }})"
                                        @class([
                                            'p-2.5 rounded-xl border text-left transition flex flex-col justify-between gap-1',
                                            'bg-primary-50 dark:bg-primary-950/40 border-primary-500 text-primary-900 dark:text-primary-100 ring-2 ring-primary-500/20' => $isSelected,
                                            'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' => ! $isSelected,
                                        ])
                                    >
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-xs font-bold">{{ $size['name'] }}</span>
                                            @if ($isSelected)
                                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                        <span class="text-[11px] {{ $size['price_modifier'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-400' }}">
                                            {{ $size['price_modifier'] > 0 ? '+Rp' . number_format($size['price_modifier'], 0, ',', '.') : 'Standar' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 2. Pilihan Topping (jika ada) --}}
                    @if (! empty($modalAvailableToppings))
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide flex items-center justify-between">
                                <span>Pilih Topping Tambahan</span>
                                <span class="text-[11px] font-normal text-gray-400">Bisa lebih dari 1</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($modalAvailableToppings as $top)
                                    @php
                                        $isTopSelected = in_array($top['id'], $modalSelectedToppingIds, true);
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="toggleModalTopping({{ $top['id'] }})"
                                        @class([
                                            'p-2.5 rounded-xl border text-left transition flex items-center justify-between gap-2',
                                            'bg-primary-50 dark:bg-primary-950/40 border-primary-500 text-primary-900 dark:text-primary-100 ring-1 ring-primary-500/20' => $isTopSelected,
                                            'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' => ! $isTopSelected,
                                        ])
                                    >
                                        <div class="flex items-center gap-2">
                                            <div @class([
                                                'w-4 h-4 rounded flex items-center justify-center border transition',
                                                'bg-primary-600 border-primary-600 text-white' => $isTopSelected,
                                                'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700' => ! $isTopSelected,
                                            ])>
                                                @if ($isTopSelected)
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium">{{ $top['name'] }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                            +Rp{{ number_format($top['price'], 0, ',', '.') }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 3. Kuantitas & Diskon Item --}}
                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Kuantitas (Qty)
                            </label>
                            <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700/60 rounded-xl p-1 border border-gray-200 dark:border-gray-600">
                                <button
                                    type="button"
                                    wire:click="decrementModalQty"
                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center justify-center shadow-xs"
                                >
                                    -
                                </button>
                                <span class="flex-grow text-center text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $modalQty }}
                                </span>
                                <button
                                    type="button"
                                    wire:click="incrementModalQty"
                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center justify-center shadow-xs"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Diskon Item (Rp)
                            </label>
                            <input
                                type="number"
                                wire:model.live.debounce.300ms="modalDiscount"
                                min="0"
                                placeholder="0"
                                class="w-full py-1.5 px-3 text-sm bg-gray-50 dark:bg-gray-700/60 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                            />
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block">Total Item ini:</span>
                        <span class="text-base font-black text-primary-600 dark:text-primary-400">
                            Rp{{ number_format($modalItemSubtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="closeCustomModal"
                            class="px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="addCustomizedItem"
                            class="px-4 py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 rounded-xl shadow-md shadow-primary-500/20 transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah ke Pesanan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
