@php
    use App\Enums\PaymentStatus;
    use App\Filament\Resources\OrderResource;
    use App\Filament\Resources\PaymentResource;
    use Illuminate\Support\Number;

    $statusClass = [
        'captured' => 'pos-status-captured',
        'pending'  => 'pos-status-pending',
        'failed'   => 'pos-status-failed',
        'expired'  => 'pos-status-expired',
        'refunded' => 'pos-status-refunded',
    ];

    $accentClass = [
        'captured' => 'pos-accent-captured',
        'pending'  => 'pos-accent-pending',
        'failed'   => 'pos-accent-failed',
        'expired'  => 'pos-accent-expired',
        'refunded' => 'pos-accent-refunded',
    ];

    $dotClass = [
        'captured' => 'pos-dot-captured',
        'pending'  => 'pos-dot-pending',
        'failed'   => 'pos-dot-failed',
        'expired'  => 'pos-dot-expired',
        'refunded' => 'pos-dot-refunded',
    ];

    $methodClass = [
        'cash'     => 'pos-method-cash',
        'qris'     => 'pos-method-qris',
        'transfer' => 'pos-method-transfer',
        'ewallet'  => 'pos-method-ewallet',
    ];

    $orderTypeClass = [
        'dine_in'   => 'pos-type-dine_in',
        'take_away' => 'pos-type-take_away',
        'delivery'  => 'pos-type-delivery',
    ];

    $orderTypeIcons = [
        'dine_in'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />',
        'take_away' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
        'delivery'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.317-5.077A1.5 1.5 0 0 0 18.066 11.25H16.5V14.25m-1.5 4.5V14.25m0 0V8.25m0 10.5h-6m6-10.5h-3.75a1.125 1.125 0 0 0-1.125 1.125v3.75m9 0h-9m9 0a1.125 1.125 0 0 1 1.125 1.125v1.5a1.125 1.125 0 0 1-1.125 1.125H16.5" />',
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

        <!-- Top Control Bar (View Mode Switcher) -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                {{ __('Pilih tampilan daftar pembayaran yang paling nyaman untuk alur kasir POS Anda.') }}
            </div>

            <div class="inline-flex rounded-2xl border border-gray-200/80 dark:border-white/10 bg-gray-100 dark:bg-white/5 p-1 text-sm font-semibold text-gray-700 dark:text-white/70 self-start sm:self-auto shadow-inner">
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    wire:loading.attr="disabled"
                    @class([
                        'flex items-center gap-2 rounded-xl px-4 py-2 transition-all duration-200 font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500',
                        'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-md' => $viewMode === 'list',
                        'opacity-70 hover:opacity-100' => $viewMode !== 'list',
                    ])
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 17.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <span>{{ __('List View') }}</span>
                </button>

                <button
                    type="button"
                    wire:click="setViewMode('card')"
                    wire:loading.attr="disabled"
                    @class([
                        'flex items-center gap-2 rounded-xl px-4 py-2 transition-all duration-200 font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500',
                        'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-md' => $viewMode === 'card',
                        'opacity-70 hover:opacity-100' => $viewMode !== 'card',
                    ])
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-1.8 2.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
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
                <!-- Search & Filters -->
                <div class="flex flex-col gap-4 rounded-2xl border border-gray-200/80 dark:border-white/[.06] bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-sm dark:shadow-none md:flex-row md:items-center md:justify-between">
                    <div class="space-y-0.5">
                        <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('Pencarian Pembayaran') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Filter dan cari pembayaran berdasarkan nama tamu, ID order, nominal bayar, atau metode.') }}
                        </p>
                    </div>

                    <div class="relative w-full md:w-80">
                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="cardSearch"
                            placeholder="{{ __('Cari pembayaran...') }}"
                            class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[.03] py-2.5 pl-10 pr-10 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                        >
                        @if ($cardSearch !== '')
                            <button
                                type="button"
                                wire:click="$set('cardSearch', '')"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="pos-card-grid">
                    @forelse ($this->cardPayments as $payment)
                        @php
                            $order = $payment->order;
                            $statusKey = $payment->status instanceof PaymentStatus ? $payment->status->value : $payment->status;
                            $statusLabel = $payment->status instanceof PaymentStatus ? $payment->status->label() : (PaymentStatus::tryFrom($payment->status)?->label() ?? ucfirst((string) $payment->status));
                            $menuItems = $order?->order_items ?? collect();
                            $menuCount = $menuItems->count();
                            $previewLimit = 2;
                            $previewItems = $menuCount > $previewLimit ? $menuItems->take($previewLimit) : $menuItems;
                            $orderTypeIcon = $order ? ($orderTypeIcons[$order->order_type] ?? null) : null;
                            $isPending = $statusKey === 'pending';
                        @endphp

                        <article class="pos-order-card">
                            <!-- Top Status Accent Stripe -->
                            <div class="pos-accent-stripe {{ $accentClass[$statusKey] ?? 'pos-accent-draft' }}"></div>

                            <div class="flex flex-1 flex-col p-4 pb-3">
                                <!-- Top Bar: Ticket Token, Time, Status Badge -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="pos-ticket-token">
                                            #PAY-{{ $payment->id }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-gray-400">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ optional($payment->created_at)->timezone(config('app.timezone'))->translatedFormat('H:i') }}
                                        </span>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="pos-status-badge {{ $statusClass[$statusKey] ?? 'pos-status-draft' }}">
                                        @if($isPending)
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $dotClass[$statusKey] ?? 'pos-dot-draft' }}"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotClass[$statusKey] ?? 'pos-dot-draft' }}"></span>
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full h-2 w-2 {{ $dotClass[$statusKey] ?? 'pos-dot-draft' }}"></span>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <!-- Customer Header & Order Reference -->
                                <div class="flex items-center gap-2 mb-2.5 min-w-0">
                                    <div class="pos-customer-avatar">
                                        {{ mb_substr($order?->customer_name ?: 'T', 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white truncate leading-snug" title="{{ $order?->customer_name ?: __('Tamu') }}">
                                            {{ $order?->customer_name ?: __('Tamu') }}
                                        </h2>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono font-medium">
                                            {{ __('Order #:number', ['number' => $order?->id ?? '-']) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Badges Row (Payment Method, Channel, Service Type, Table) -->
                                <div class="flex flex-wrap items-center gap-1.5 mb-3">
                                    <!-- Payment Method Pill -->
                                    <span class="pos-method-badge {{ $methodClass[$payment->payment_method] ?? '' }}">
                                        @if($payment->payment_method === 'cash')
                                            <span>💵</span>
                                        @elseif($payment->payment_method === 'qris')
                                            <span>📱</span>
                                        @elseif($payment->payment_method === 'transfer')
                                            <span>🏦</span>
                                        @elseif($payment->payment_method === 'ewallet')
                                            <span>💳</span>
                                        @else
                                            <span>🪙</span>
                                        @endif
                                        <span>{{ str($payment->payment_method ?? 'unknown')->headline() }}</span>
                                        @if($payment->payment_channel)
                                            <span class="opacity-75 font-mono text-[10px]">({{ strtoupper($payment->payment_channel) }})</span>
                                        @endif
                                    </span>

                                    <!-- Service Type Pill -->
                                    @if($order)
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-semibold {{ $orderTypeClass[$order->order_type] ?? 'pos-type-dine_in' }}">
                                            @if($orderTypeIcon)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 shrink-0">{!! $orderTypeIcon !!}</svg>
                                            @endif
                                            <span>{{ in_array($order->order_type, ['dine_in', 'take_away', 'delivery']) ? __('orders.types.' . $order->order_type) : $this->getOrderTypeLabel($order->order_type) }}</span>
                                        </span>

                                        @if($order->order_type === 'dine_in' && optional($order->table)->table_number)
                                            <span class="pos-table-badge inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-bold">
                                                <span>{{ __('Meja :number', ['number' => optional($order->table)->table_number]) }}</span>
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                <!-- POS Receipt Financial Box -->
                                <div
                                    wire:click="openPaymentDetailModal({{ $payment->id }})"
                                    class="pos-financial-receipt mb-3 cursor-pointer hover:border-amber-400 dark:hover:border-amber-500 transition-colors group/receipt"
                                    title="{{ __('Klik untuk lihat rincian struk POS') }}"
                                >
                                    <div class="flex justify-between items-center text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                                        <span>{{ __('Total Tagihan') }}</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">
                                            {{ Number::currency($order?->total_order ?? $order?->total ?? $payment->amount_paid ?? 0, 'IDR', locale: app()->getLocale()) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs font-bold border-t border-dotted border-gray-300 dark:border-gray-700 pt-1.5 mb-1">
                                        <span class="text-gray-800 dark:text-gray-200">{{ __('Jumlah Bayar') }}</span>
                                        <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400 font-mono">
                                            {{ Number::currency($payment->amount_paid ?? 0, 'IDR', locale: app()->getLocale()) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px] border-t border-dotted border-gray-300 dark:border-gray-700 pt-1">
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Kembalian') }}</span>
                                        @if(($payment->change_return ?? 0) > 0)
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ Number::currency($payment->change_return, 'IDR', locale: app()->getLocale()) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 font-mono">
                                                IDR 0
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Receipt Menu Items Summary -->
                                <div x-data="{ expanded: false }" class="pos-receipt-box flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">
                                                {{ trans_choice('orders.menu_count', $menuCount, ['count' => $menuCount]) }}
                                            </span>

                                            @if ($menuCount > $previewLimit)
                                                <button
                                                    type="button"
                                                    class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 focus:outline-none transition-colors"
                                                    x-on:click.stop="expanded = !expanded"
                                                >
                                                    <span x-text="expanded ? '{{ __('Sembunyikan') }}' : '{{ __('Lihat semua') }}'"></span>
                                                </button>
                                            @endif
                                        </div>

                                        @if ($menuCount > 0)
                                            <ul x-show="!expanded" class="space-y-1.5">
                                                @foreach ($previewItems as $item)
                                                    <li
                                                        wire:click="openPaymentDetailModal({{ $payment->id }})"
                                                        class="flex cursor-pointer items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/5 transition-colors group/item"
                                                        title="{{ __('Klik untuk lihat rincian struk') }}"
                                                    >
                                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                                            <span class="pos-qty-badge">
                                                                {{ $item->qty ?? 0 }}×
                                                            </span>
                                                            <span class="truncate font-medium group-hover/item:text-amber-600 dark:group-hover/item:text-amber-400 transition-colors">
                                                                {{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}
                                                            </span>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            @if ($menuCount > $previewLimit)
                                                <ul
                                                    x-show="expanded"
                                                    x-cloak
                                                    class="space-y-1.5"
                                                >
                                                    @foreach ($menuItems as $item)
                                                        <li
                                                            wire:click="openPaymentDetailModal({{ $payment->id }})"
                                                            class="flex cursor-pointer items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/5 transition-colors group/item"
                                                            title="{{ __('Klik untuk lihat rincian struk') }}"
                                                        >
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                <span class="pos-qty-badge">
                                                                    {{ $item->qty ?? 0 }}×
                                                                </span>
                                                                <span class="truncate font-medium group-hover/item:text-amber-600 dark:group-hover/item:text-amber-400 transition-colors">
                                                                    {{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @else
                                            <p class="text-xs text-gray-400 dark:text-gray-500 italic py-1">{{ __('Belum ada rincian menu.') }}</p>
                                        @endif
                                    </div>

                                    @if(optional($order?->user)->name || optional($payment->confirmedBy)->name)
                                        <div class="mt-2.5 pt-2 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500">
                                            <span>
                                                {{ __('Kasir') }}:
                                                <strong class="font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ optional($payment->confirmedBy)->name ?? optional($order?->user)->name }}
                                                </strong>
                                            </span>
                                            <span>{{ optional($payment->updated_at)->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Ticket Tear Line (Dashed) -->
                            <div class="relative flex items-center px-4">
                                <div class="w-full border-t border-dashed border-gray-200 dark:border-gray-700"></div>
                            </div>

                            <!-- Footer: Total & Actions -->
                            <div class="p-4 pt-3 bg-gray-50/50 dark:bg-white/[0.01] flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 font-bold block leading-none mb-1">
                                        {{ __('Total Bayar') }}
                                    </span>
                                    <span class="text-base font-black text-gray-900 dark:text-white font-mono tracking-tight truncate block">
                                        {{ Number::currency($payment->amount_paid ?? 0, 'IDR', locale: app()->getLocale()) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- View Receipt Button -->
                                    <button
                                        type="button"
                                        wire:click="openPaymentDetailModal({{ $payment->id }})"
                                        class="pos-btn-detail"
                                        title="{{ __('Lihat Struk / Detail Pembayaran') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </button>

                                    <!-- Print Thermal Receipt Button -->
                                    @if($payment->order_id && ($payment->status === \App\Enums\PaymentStatus::Captured || $payment->status === 'captured' || (string) $payment->status === 'captured'))
                                        <a
                                            href="{{ route('payments.print', ['payment' => $payment]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="pos-btn-print"
                                            title="{{ __('Cetak Struk Thermal') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" />
                                            </svg>
                                        </a>

                                        <!-- WhatsApp E-Receipt Button -->
                                        <a
                                            href="{{ PaymentResource::generateWhatsappLink($payment) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="pos-btn-print bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60"
                                            title="{{ __('Kirim Struk WhatsApp') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                            </svg>
                                        </a>
                                    @endif

                                    <!-- Manage Payment Button -->
                                    <a
                                        href="{{ PaymentResource::getUrl('edit', ['record' => $payment]) }}"
                                        class="pos-btn-kelola"
                                        title="{{ __('Kelola Pembayaran') }}"
                                    >
                                        <span>{{ __('Kelola') }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/20 p-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-700 mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('Belum ada pembayaran yang cocok') }}</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                                {{ __('Coba ubah kata kunci pencarian atau beralih ke List View untuk melihat data lengkap.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($this->cardPayments->isNotEmpty() && $this->getFilteredTableQuery()->count() > $this->cardPayments->count())
                    <div class="rounded-xl bg-gray-50 dark:bg-white/[.02] border border-gray-100 dark:border-white/[.05] p-3.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                        {{ __('Menampilkan :count pembayaran terbaru. Gunakan List View untuk riwayat lengkap.', ['count' => $this->cardPayments->count()]) }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- POS Thermal Receipt Modal (Payment Details)                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    @if ($isDetailModalOpen && $detailPaymentId)
        @php
            $paymentModalId = 'payment-detail-modal';
        @endphp

        <div
            x-data="{
                show: true,
                close() {
                    this.show = false;
                    $wire.closePaymentDetailModal();
                }
            }"
            x-show="show"
            x-on:keydown.escape.window="close()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            id="{{ $paymentModalId }}"
            class="pos-modal-backdrop fixed inset-0 z-[99999] flex items-center justify-center bg-black/75 dark:bg-black/85 p-3 sm:p-6 backdrop-blur-sm overflow-y-auto"
            style="position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.75); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);"
            @click.self="close()"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="pos-modal-dialog relative w-full max-w-md my-auto font-sans"
                style="max-width: 28rem; width: 100%; position: relative; z-index: 100000;"
            >
                {{-- Close Button --}}
                <button
                    type="button"
                    wire:click="closePaymentDetailModal"
                    x-on:click="close()"
                    class="absolute -top-2 -right-2 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white shadow-lg border border-gray-200 dark:border-gray-700 transition-colors"
                    title="{{ __('Tutup') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Receipt Paper --}}
                <div class="bg-white dark:bg-gray-950 rounded-lg shadow-2xl dark:shadow-[0_25px_60px_rgba(0,0,0,0.6)] border border-gray-200/60 dark:border-gray-800 overflow-hidden font-sans">
                    {{-- ══ Receipt Header ══ --}}
                    <div class="px-6 pt-6 pb-4 text-center">
                        <div class="flex justify-center mb-3">
                            <div class="h-10 w-10 rounded-full bg-emerald-600 flex items-center justify-center shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] font-bold mb-1">
                            {{ __('BUKTI PEMBAYARAN') }}
                        </p>

                        <h2 class="text-xl font-black text-gray-900 dark:text-white font-mono tracking-tight">
                            #PAY-{{ $detailPaymentMeta['payment_id'] ?? '-' }}
                        </h2>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Terkait Order #:number', ['number' => $detailPaymentMeta['order_id'] ?? '-']) }}
                        </p>

                        {{-- Status Badge --}}
                        <div class="flex justify-center mt-2.5">
                            <span class="pos-status-badge {{ $statusClass[$detailPaymentMeta['status'] ?? ''] ?? 'pos-status-draft' }}">
                                @if(($detailPaymentMeta['status'] ?? '') === 'pending')
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                    </span>
                                @endif
                                {{ $detailPaymentMeta['status_label'] ?? ucfirst($detailPaymentMeta['status'] ?? '-') }}
                            </span>
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Meta Info ══ --}}
                    <div class="px-6 py-3 text-[11px] text-gray-600 dark:text-gray-400 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">{{ __('Pelanggan') }}</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $detailPaymentMeta['customer_name'] ?? 'Tamu' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">{{ __('Waktu Bayar') }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $detailPaymentMeta['paid_at'] ?? $detailPaymentMeta['created_at'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">{{ __('Metode Bayar') }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                {{ str($detailPaymentMeta['payment_method'] ?? 'unknown')->headline() }}
                                @if(!empty($detailPaymentMeta['payment_channel']))
                                    ({{ strtoupper($detailPaymentMeta['payment_channel']) }})
                                @endif
                            </span>
                        </div>
                        @if(!empty($detailPaymentMeta['provider']) || !empty($detailPaymentMeta['external_reference']))
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">{{ __('Ref / Provider') }}</span>
                                <span class="font-mono text-[10px] text-gray-700 dark:text-gray-300">
                                    {{ $detailPaymentMeta['provider'] ?? '' }} {{ $detailPaymentMeta['external_reference'] ?? '' }}
                                </span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">{{ __('Kasir') }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $detailPaymentMeta['cashier_name'] ?? '-' }}</span>
                        </div>
                        @if(!empty($detailPaymentMeta['confirmed_by']))
                            <div class="flex justify-between">
                                <span class="text-gray-400 dark:text-gray-500">{{ __('Dikonfirmasi Oleh') }}</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $detailPaymentMeta['confirmed_by'] }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-400 dark:text-gray-500">{{ __('Tipe Pesanan') }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                {{ $detailPaymentMeta['order_type_label'] ?? '-' }}
                                @if(!empty($detailPaymentMeta['table_number']))
                                    · {{ __('Meja :number', ['number' => $detailPaymentMeta['table_number']]) }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Order Items ══ --}}
                    <div class="px-6 py-3">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em] font-bold mb-2 text-center">
                            {{ __('Rincian Pesanan') }}
                        </p>

                        <div class="space-y-0">
                            @forelse ($detailOrderItems as $item)
                                <div class="py-2 {{ !$loop->last ? 'border-b border-dotted border-gray-200 dark:border-gray-800' : '' }}">
                                    <div class="flex justify-between items-start gap-2 text-xs">
                                        <span class="font-semibold text-gray-800 dark:text-white flex-1">{{ $item['name'] }}</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 shrink-0 font-mono">
                                            {{ Number::currency($item['subtotal'], 'IDR', locale: app()->getLocale()) }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-mono">
                                        {{ $item['qty'] }} × {{ Number::currency($item['price'], 'IDR', locale: app()->getLocale()) }}
                                    </p>

                                    @if (! empty($item['toppings']))
                                        <div class="mt-1 space-y-0.5 pl-3">
                                            @foreach ($item['toppings'] as $topping)
                                                <div class="flex justify-between text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                                    <span class="flex items-center gap-1">
                                                        <span class="text-amber-500">+</span>
                                                        <span>{{ $topping['name'] }}</span>
                                                    </span>
                                                    <span>{{ $topping['quantity'] }} × {{ Number::currency($topping['price'], 'IDR', locale: app()->getLocale()) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 dark:text-gray-600 italic py-3 text-center">{{ __('Tidak ada item terkait.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Subtotal & Breakdown ══ --}}
                    <div class="px-6 py-3 space-y-1.5 text-xs font-mono">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ Number::currency($detailPaymentMeta['subtotal_order'] ?? 0, 'IDR', locale: app()->getLocale()) }}</span>
                        </div>
                        @if (($detailPaymentMeta['discount_order'] ?? 0) > 0)
                            <div class="flex justify-between text-rose-500 dark:text-rose-400">
                                <span>{{ __('Diskon') }}</span>
                                <span class="font-semibold">-{{ Number::currency($detailPaymentMeta['discount_order'], 'IDR', locale: app()->getLocale()) }}</span>
                            </div>
                        @endif
                        @if (($detailPaymentMeta['service_fee_order'] ?? 0) > 0)
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>{{ __('Biaya Layanan') }}</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ Number::currency($detailPaymentMeta['service_fee_order'], 'IDR', locale: app()->getLocale()) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- ══ Financial Totals (Double Border) ══ --}}
                    <div class="mx-5 border-t-2 border-double border-gray-400 dark:border-gray-600"></div>
                    <div class="px-6 py-3 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">{{ __('Total Tagihan') }}</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white font-mono">
                                {{ Number::currency($detailPaymentMeta['total_order'] ?? 0, 'IDR', locale: app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ __('Jumlah Bayar') }}</span>
                            <span class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono">
                                {{ Number::currency($detailPaymentMeta['amount_paid'] ?? 0, 'IDR', locale: app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs border-t border-dotted border-gray-300 dark:border-gray-700 pt-1.5">
                            <span class="font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">{{ __('Kembalian') }}</span>
                            <span class="text-sm font-black font-mono {{ ($detailPaymentMeta['change_return'] ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500' }}">
                                {{ Number::currency($detailPaymentMeta['change_return'] ?? 0, 'IDR', locale: app()->getLocale()) }}
                            </span>
                        </div>
                    </div>
                    <div class="mx-5 border-t-2 border-double border-gray-400 dark:border-gray-600"></div>

                    {{-- ══ Thank You Footer ══ --}}
                    <div class="px-6 py-4 text-center">
                        <p class="text-[10px] text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] font-semibold">
                            {{ __('Terima Kasih atas Pembayaran Anda') }}
                        </p>
                        <p class="text-[9px] text-gray-300 dark:text-gray-700 mt-1">
                            ★ ★ ★ ★ ★
                        </p>
                    </div>
                </div>

                {{-- ══ Action Buttons (below receipt) ══ --}}
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:justify-end font-sans">
                    <x-filament::button
                        color="gray"
                        wire:click="closePaymentDetailModal"
                        x-on:click="close()"
                        size="lg"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Tutup') }}
                    </x-filament::button>

                    @if(!empty($detailPaymentMeta['order_id']))
                        <x-filament::button
                            tag="a"
                            color="gray"
                            :href="\App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $detailPaymentMeta['order_id']])"
                            size="lg"
                            class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                        >
                            {{ __('Lihat Order') }}
                        </x-filament::button>
                    @endif

                    <x-filament::button
                        tag="a"
                        color="primary"
                        :href="PaymentResource::getUrl('edit', ['record' => $detailPaymentId])"
                        size="lg"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Kelola Pembayaran') }}
                    </x-filament::button>

                    @if($detailPaymentId)
                        @php
                            $waPayment = \App\Models\Payment::find($detailPaymentId);
                            $waLink = $waPayment ? PaymentResource::generateWhatsappLink($waPayment) : '#';
                        @endphp
                        @if($waPayment && $waPayment->isCaptured())
                            <x-filament::button
                                tag="a"
                                color="success"
                                :href="$waLink"
                                target="_blank"
                                size="lg"
                                icon="heroicon-o-paper-airplane"
                                class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                            >
                                {{ __('Kirim WA') }}
                            </x-filament::button>
                        @endif
                    @endif

                    <x-filament::button
                        tag="a"
                        color="info"
                        :href="route('payments.print', ['payment' => $detailPaymentId])"
                        target="_blank"
                        size="lg"
                        icon="heroicon-o-printer"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Cetak Struk Thermal') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
