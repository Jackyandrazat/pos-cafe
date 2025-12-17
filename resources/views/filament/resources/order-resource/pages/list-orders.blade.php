@php
    use App\Filament\Resources\OrderResource;
    use Illuminate\Support\Number;

    $statusStyles = [
        'open' => 'border-amber-500/30 bg-amber-400/10 text-amber-200',
        'completed' => 'border-emerald-500/30 bg-emerald-400/10 text-emerald-200',
        'cancelled' => 'border-rose-500/30 bg-rose-400/10 text-rose-200',
    ];
@endphp

<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-white/60">
                {{ __('Pilih tampilan daftar order yang paling nyaman untuk alur kerja Anda.') }}
            </div>

            <div class="inline-flex rounded-2xl border border-white/10 bg-white/5 p-1 text-sm font-medium text-white/70">
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    wire:loading.attr="disabled"
                    @class([
                        'flex items-center gap-2 rounded-xl px-4 py-1.5 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/60',
                        'bg-white/90 text-slate-900 shadow-lg shadow-white/20' => $viewMode === 'list',
                        'opacity-70 hover:opacity-100' => $viewMode !== 'list',
                    ])
                >
                    <span>{{ __('List View') }}</span>
                </button>

                <button
                    type="button"
                    wire:click="setViewMode('card')"
                    wire:loading.attr="disabled"
                    @class([
                        'flex items-center gap-2 rounded-xl px-4 py-1.5 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/60',
                        'bg-white/90 text-slate-900 shadow-lg shadow-white/20' => $viewMode === 'card',
                        'opacity-70 hover:opacity-100' => $viewMode !== 'card',
                    ])
                >
                    <span>{{ __('Card View') }}</span>
                </button>
            </div>
        </div>

        @if ($viewMode === 'list')
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            {{ $this->table }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
        @else
            <div class="space-y-6">
                <div class="flex flex-col gap-3 rounded-3xl border border-white/10 bg-white/5 p-4 text-white/70 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm">
                        {{ __('Filter dan cari order di tampilan kartu. Ketik nama pelanggan, nomor meja, menu, atau status.') }}
                    </div>

                    <div class="relative w-full sm:w-80">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-white/40">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 15.5L21 21" />
                                <circle cx="10.5" cy="10.5" r="6" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="cardSearch"
                            placeholder="{{ __('Cari order...') }}"
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/60 py-2 pl-10 pr-3 text-sm text-white placeholder:text-white/40 focus:border-amber-300/60 focus:outline-none focus:ring-2 focus:ring-amber-300/40"
                        >
                        @if ($cardSearch !== '')
                            <button
                                type="button"
                                wire:click="$set('cardSearch', '')"
                                class="absolute inset-y-0 right-2 flex items-center text-white/40 transition hover:text-white/80"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid gap-6 grid-cols-1 md:grid-cols-3">
                    @forelse ($this->cardOrders as $order)
                        <article class="flex h-full flex-col gap-6 rounded-3xl bg-slate-950/80 p-6 shadow-[0_25px_60px_rgba(2,6,23,0.55)] ring-1 ring-white/5 transition hover:ring-amber-400/40">
                            <header class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/45">{{ __('Order #:number', ['number' => $order->id]) }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold text-white">
                                        {{ $order->customer_name ?: __('Tamu') }}
                                    </h2>
                                </div>

                                <span @class([
                                    'inline-flex items-center rounded-full border px-4 py-1 text-sm font-semibold uppercase tracking-wide',
                                    $statusStyles[$order->status] ?? 'border-white/20 bg-white/10 text-white/80',
                                ])>
                                    {{ __('orders.status.' . ($order->status ?? 'unknown')) }}
                                </span>
                            </header>

                            <dl class="grid gap-4 text-sm text-white/70 sm:grid-cols-2">
                                <div class="flex flex-col gap-1 rounded-2xl border border-white/5 bg-white/5 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Order Type') }}</dt>
                                    <dd class="text-base font-semibold text-white">
                                        {{ $this->getOrderTypeLabel($order->order_type) }}
                                    </dd>
                                    <dd class="text-sm text-white/60">
                                        {{ optional($order->table)->table_number ? __('Meja :number', ['number' => optional($order->table)->table_number]) : __('Tanpa meja') }}
                                    </dd>
                                </div>

                                <div class="flex flex-col gap-1 rounded-2xl border border-white/5 bg-white/5 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Total') }}</dt>
                                    <dd class="text-3xl font-bold text-amber-300">
                                        {{ Number::currency($order->total_order ?? 0, 'IDR', locale: 'id') }}
                                    </dd>
                                    @php
                                        $menuItems = ($order->order_items ?? collect());
                                        $menuCount = $menuItems->count();
                                        $previewLimit = 3;
                                        $previewItems = $menuCount > $previewLimit ? $menuItems->take($previewLimit) : $menuItems;
                                    @endphp
                                    <dd x-data="{ expanded: false }" class="flex flex-col gap-3 text-sm text-white/60">
                                        <div class="flex items-center justify-between gap-2">
                                            <span>{{ trans_choice('{0} Tidak ada menu|{1} :count menu|[2,*] :count menu', $menuCount, ['count' => $menuCount]) }}</span>

                                            @if ($menuCount > $previewLimit)
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-200 transition hover:text-amber-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/40"
                                                    x-on:click="expanded = !expanded"
                                                >
                                                    <span x-text="expanded ? '{{ __('Sembunyikan menu') }}' : '{{ __('Lihat semua menu') }}'"></span>
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        class="h-4 w-4 transition"
                                                        :class="expanded ? 'rotate-180' : ''"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        @if ($menuCount > 0)
                                            <ul class="space-y-1 rounded-2xl border border-white/5 bg-white/5 p-3 text-white/80">
                                                @foreach ($previewItems as $item)
                                                    <li
                                                        wire:click="openOrderDetailModal({{ $order->id }})"
                                                        class="flex cursor-pointer items-center justify-between rounded-xl px-2 py-1 text-xs font-medium transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/40 sm:text-sm"
                                                        title="{{ __('Lihat detail order') }}"
                                                    >
                                                        <span class="flex-1 truncate pr-2">{{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}</span>
                                                        <span class="text-white/40">×{{ $item->qty ?? 0 }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            @if ($menuCount > $previewLimit)
                                                <div
                                                    x-show="expanded"
                                                    x-transition
                                                    x-cloak
                                                    class="rounded-2xl border border-amber-400/20 bg-slate-950/70 p-4 text-white/80"
                                                >
                                                    <h4 class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200">Daftar Menu Lengkap</h4>
                                                    <ul class="mt-3 space-y-1 text-xs font-medium sm:text-sm">
                                                        @foreach ($menuItems as $item)
                                                            <li
                                                                wire:click="openOrderDetailModal({{ $order->id }})"
                                                                class="flex cursor-pointer items-center justify-between rounded-xl px-2 py-1 transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/40"
                                                                title="{{ __('Lihat detail order') }}"
                                                            >
                                                                <span class="flex-1 truncate pr-2">{{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}</span>
                                                                <span class="text-white/40">×{{ $item->qty ?? 0 }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-white/40">{{ __('Belum ada menu pada order ini.') }}</span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="rounded-2xl border border-white/5 bg-white/5 p-4 sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Waktu Order') }}</dt>
                                    <dd class="mt-2 text-base font-semibold text-white">
                                        {{ optional($order->created_at)->timezone(config('app.timezone'))->translatedFormat('d M Y • H:i') }}
                                    </dd>
                                </div>
                            </dl>

                            <footer class="flex flex-col gap-3 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
                                <span>
                                    {{ __('Terakhir diperbarui :time', ['time' => optional($order->updated_at)->diffForHumans() ?? '-']) }}
                                </span>

                                <x-filament::button
                                    size="lg"
                                    tag="a"
                                    color="primary"
                                    class="w-full sm:w-auto"
                                    :href="OrderResource::getUrl('edit', ['record' => $order])"
                                >
                                    {{ __('Kelola Order') }}
                                </x-filament::button>
                            </footer>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-white/15 bg-white/5 p-8 text-center text-white/70">
                            <h3 class="text-lg font-semibold text-white">{{ __('Belum ada order dalam tampilan ini') }}</h3>
                            <p class="mt-2 text-sm text-white/60">
                                {{ __('Gunakan tombol “Tambah Order” untuk mulai mencatat transaksi baru atau beralih ke List View untuk melihat data lebih lengkap.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($this->cardOrders->isNotEmpty() && $this->getFilteredTableQuery()->count() > $this->cardOrders->count())
                    <p class="text-center text-sm text-white/60">
                        {{ __('Menampilkan :count order terbaru. Gunakan List View untuk mengakses seluruh riwayat lengkap.', ['count' => $this->cardOrders->count()]) }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    @if ($detailOrderId)
        @php
            $modalId = 'order-detail-modal';
            $detailStatusStyle = $statusStyles[$detailOrderMeta['status'] ?? ''] ?? 'border-gray-200 bg-gray-100 text-gray-700';
        @endphp

        <div
            x-data="{
                show: @js($isDetailModalOpen),
                close() {
                    this.show = false;
                    $wire.closeOrderDetailModal();
                }
            }"
            x-cloak
            x-show="show"
            x-transition.opacity.duration.200ms
            id="{{ $modalId }}"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 px-4 py-6 backdrop-blur"
        >
            <div
                x-show="show"
                x-transition.scale.origin-center.duration.200ms
                @click.away="close()"
                class="relative w-full max-w-4xl rounded-3xl border border-white/10 bg-white p-6 text-gray-900 shadow-2xl"
            >
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Order #:number', ['number' => $detailOrderMeta['order_number'] ?? '-']) }}</p>
                        <h2 class="mt-1 text-2xl font-semibold text-gray-900">{{ $detailOrderMeta['customer_name'] ?? 'Tamu' }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $detailOrderMeta['order_type_label'] ?? '' }} •
                            {{ $detailOrderMeta['table_number'] ? __('Meja :number', ['number' => $detailOrderMeta['table_number']]) : __('Tanpa meja') }}
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-4 py-1 text-sm font-semibold uppercase tracking-wide {{ $detailStatusStyle }}">
                        {{ $detailOrderMeta['status_label'] ?? __('orders.status.unknown') }}
                    </span>
                </div>

                <div class="mt-6 space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ __('Total Bayar') }}</p>
                            <p class="mt-2 text-3xl font-bold text-amber-600">{{ Number::currency($detailOrderMeta['total_order'] ?? 0, 'IDR', locale: 'id') }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ __('Waktu Order') }}</p>
                            <p class="mt-2 text-base font-semibold">{{ $detailOrderMeta['created_at'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="max-h-[50vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Detail Menu & Topping') }}</h3>

                        <div class="mt-4 space-y-4">
                            @forelse ($detailOrderItems as $item)
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="text-base font-semibold text-gray-900">{{ $item['name'] }}</p>
                                            <p class="text-sm text-gray-600">×{{ $item['qty'] }} • {{ Number::currency($item['price'], 'IDR', locale: 'id') }}</p>
                                        </div>
                                        <p class="text-base font-semibold text-amber-600">{{ Number::currency($item['subtotal'], 'IDR', locale: 'id') }}</p>
                                    </div>

                                    @if (! empty($item['toppings']))
                                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">{{ __('Topping') }}</p>
                                            <ul class="mt-2 space-y-1 text-sm text-amber-900">
                                                @foreach ($item['toppings'] as $topping)
                                                    <li class="flex flex-wrap items-center justify-between gap-2">
                                                        <span>{{ $topping['name'] }}</span>
                                                        <span class="text-sm text-amber-800">×{{ $topping['quantity'] }} • {{ Number::currency($topping['price'], 'IDR', locale: 'id') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="mt-3 text-xs text-gray-500">{{ __('Tidak ada topping untuk menu ini.') }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('Belum ada item pada order ini.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                    <x-filament::button color="gray" x-on:click="close()">{{ __('Tutup') }}</x-filament::button>
                    <x-filament::button
                        tag="a"
                        color="primary"
                        :href="\App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $detailOrderId])"
                    >
                        {{ __('Kelola Order') }}
                    </x-filament::button>
                </div>

                <button
                    type="button"
                    class="absolute right-4 top-4 text-gray-400 transition hover:text-gray-600"
                    x-on:click="close()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
