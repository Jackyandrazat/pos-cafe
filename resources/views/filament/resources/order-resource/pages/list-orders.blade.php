@php
    use App\Filament\Resources\OrderResource;
    use Illuminate\Support\Number;

    $statusStyles = [
        'open' => 'border-amber-200 bg-amber-50 text-amber-850 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-850 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
        'cancelled' => 'border-rose-200 bg-rose-50 text-rose-850 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300',
    ];

    $statusAccent = [
        'open' => 'border-l-amber-400 dark:border-l-amber-500',
        'completed' => 'border-l-emerald-400 dark:border-l-emerald-500',
        'cancelled' => 'border-l-rose-400 dark:border-l-rose-500',
    ];

    $orderTypeBadge = [
        'dine_in' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
        'take_away' => 'bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400',
        'delivery' => 'bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400',
    ];

    $orderTypeIcons = [
        'dine_in' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />',
        'take_away' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
        'delivery' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.317-5.077A1.5 1.5 0 0 0 18.066 11.25H16.5V14.25m-1.5 4.5V14.25m0 0V8.25m0 10.5h-6m6-10.5h-3.75a1.125 1.125 0 0 0-1.125 1.125v3.75m9 0h-9m9 0a1.125 1.125 0 0 1 1.125 1.125v1.5a1.125 1.125 0 0 1-1.125 1.125H16.5" />',
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
            <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                {{ __('Pilih tampilan daftar order yang paling nyaman untuk alur kerja Anda.') }}
            </div>

            <div class="inline-flex rounded-2xl border border-gray-200/80 dark:border-white/10 bg-gray-150/70 dark:bg-white/5 p-1 text-sm font-semibold text-slate-700 dark:text-white/70 self-start sm:self-auto shadow-inner">
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    wire:loading.attr="disabled"
                    @class([
                        'flex items-center gap-2 rounded-xl px-4 py-2 transition-all duration-200 font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500',
                        'bg-white dark:bg-gray-800 text-slate-900 dark:text-white shadow-md shadow-gray-250/20 dark:shadow-none scale-[1.02]' => $viewMode === 'list',
                        'opacity-70 hover:opacity-100 hover:scale-[1.02] active:scale-[0.98]' => $viewMode !== 'list',
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
                        'bg-white dark:bg-gray-800 text-slate-900 dark:text-white shadow-md shadow-gray-250/20 dark:shadow-none scale-[1.02]' => $viewMode === 'card',
                        'opacity-70 hover:opacity-100 hover:scale-[1.02] active:scale-[0.98]' => $viewMode !== 'card',
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
                <div class="flex flex-col gap-4 rounded-2xl border border-gray-200/60 dark:border-white/[.06] bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-sm dark:shadow-none md:flex-row md:items-center md:justify-between">
                    <div class="space-y-0.5">
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Pencarian & Filter') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Ketik nama pelanggan, nomor meja, menu, atau status untuk menyaring daftar.') }}
                        </p>
                    </div>

                    <div class="relative w-full md:w-80">
                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400 dark:text-white/40">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="cardSearch"
                            placeholder="{{ __('Cari order...') }}"
                            class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[.03] py-2.5 pl-10 pr-10 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/30 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                        >
                        @if ($cardSearch !== '')
                            <button
                                type="button"
                                wire:click="$set('cardSearch', '')"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 dark:text-white/40 transition hover:text-slate-600 dark:hover:text-white/80"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    @forelse ($this->cardOrders as $order)
                        @php
                            $menuItems = ($order->order_items ?? collect());
                            $menuCount = $menuItems->count();
                            $previewLimit = 2;
                            $previewItems = $menuCount > $previewLimit ? $menuItems->take($previewLimit) : $menuItems;
                            $accent = $statusAccent[$order->status] ?? 'border-l-gray-300 dark:border-l-gray-600';
                            $typeBadgeClass = $orderTypeBadge[$order->order_type] ?? 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400';
                            $typeIcon = $orderTypeIcons[$order->order_type] ?? null;
                        @endphp

                        <article
                            class="group relative flex h-full flex-col rounded-xl border border-gray-200/70 dark:border-white/[.06] border-l-[3px] {{ $accent }} bg-white dark:bg-gray-900 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-[0_8px_24px_rgba(0,0,0,0.3)] hover:-translate-y-0.5 transition-all duration-200 ease-out overflow-hidden"
                        >
                            <div class="flex flex-1 flex-col p-4 pb-0">
                                <!-- Header: Customer & Status -->
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0">
                                        <h2 class="text-sm font-bold text-slate-900 dark:text-white truncate leading-snug" title="{{ $order->customer_name ?: __('Tamu') }}">
                                            {{ $order->customer_name ?: __('Tamu') }}
                                        </h2>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500 font-semibold">#{{ $order->id }}</span>
                                            <span class="text-slate-300 dark:text-slate-700 text-[10px]">·</span>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-2.5 h-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                {{ optional($order->created_at)->timezone(config('app.timezone'))->translatedFormat('H:i') }}
                                            </span>
                                        </div>
                                    </div>

                                    <span @class([
                                        'inline-flex items-center gap-1 shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                        $statusStyles[$order->status] ?? 'border-gray-200 bg-gray-100 text-gray-700 dark:border-white/20 dark:bg-white/10 dark:text-white/80',
                                    ])>
                                        @if($order->status === 'open')
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                            </span>
                                        @elseif($order->status === 'completed')
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                            </span>
                                        @endif
                                        {{ __('orders.status.' . ($order->status ?? 'unknown')) }}
                                    </span>
                                </div>

                                <!-- Order Type Badge -->
                                <div class="flex items-center gap-1.5 mb-3">
                                    <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $typeBadgeClass }}">
                                        @if($typeIcon)
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">{!! $typeIcon !!}</svg>
                                        @endif
                                        @if(in_array($order->order_type, ['dine_in', 'take_away', 'delivery']))
                                            {{ __('orders.types.' . $order->order_type) }}
                                        @else
                                            {{ $this->getOrderTypeLabel($order->order_type) }}
                                        @endif
                                    </span>
                                    @if($order->order_type === 'dine_in' && optional($order->table)->table_number)
                                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 text-[10px] font-semibold">
                                            {{ __('Meja :number', ['number' => optional($order->table)->table_number]) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Items List -->
                                <div x-data="{ expanded: false }" class="flex-1">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                                            {{ trans_choice('orders.menu_count', $menuCount, ['count' => $menuCount]) }}
                                        </span>

                                        @if ($menuCount > $previewLimit)
                                            <button
                                                type="button"
                                                class="text-[10px] font-bold text-amber-500 dark:text-amber-400 hover:text-amber-600 dark:hover:text-amber-300 focus:outline-none transition-colors"
                                                x-on:click.stop="expanded = !expanded"
                                            >
                                                <span x-text="expanded ? '{{ __('Sembunyikan') }}' : '{{ __('Lihat semua') }}'"></span>
                                            </button>
                                        @endif
                                    </div>

                                    @if ($menuCount > 0)
                                        <ul x-show="!expanded" class="space-y-0.5">
                                            @foreach ($previewItems as $item)
                                                <li
                                                    wire:click="openOrderDetailModal({{ $order->id }})"
                                                    class="flex cursor-pointer items-center justify-between rounded-lg px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/[.03] transition-colors"
                                                    title="{{ __('Lihat detail order') }}"
                                                >
                                                    <span class="flex-1 truncate pr-2 font-medium text-slate-700 dark:text-slate-300">{{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}</span>
                                                    <span class="font-mono text-slate-400 dark:text-slate-500 text-[10px]">×{{ $item->qty ?? 0 }}</span>
                                                </li>
                                            @endforeach
                                        </ul>

                                        @if ($menuCount > $previewLimit)
                                            <ul
                                                x-show="expanded"
                                                x-cloak
                                                class="space-y-0.5"
                                            >
                                                @foreach ($menuItems as $item)
                                                    <li
                                                        wire:click="openOrderDetailModal({{ $order->id }})"
                                                        class="flex cursor-pointer items-center justify-between rounded-lg px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/[.03] transition-colors"
                                                        title="{{ __('Lihat detail order') }}"
                                                    >
                                                        <span class="flex-1 truncate pr-2 font-medium text-slate-700 dark:text-slate-300">{{ $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id }}</span>
                                                        <span class="font-mono text-slate-400 dark:text-slate-500 text-[10px]">×{{ $item->qty ?? 0 }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @else
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic py-1">{{ __('Belum ada menu pada order ini.') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer: Total & Actions -->
                            <div class="mt-3 border-t border-gray-100 dark:border-white/[.04] bg-gray-50/50 dark:bg-white/[.02] px-4 py-3 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-semibold block leading-none mb-0.5">{{ __('Total') }}</span>
                                    <span class="text-base font-extrabold text-slate-900 dark:text-white font-mono tracking-tight">
                                        {{ Number::currency($order->total_order ?? 0, 'IDR', locale: app()->getLocale()) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openOrderDetailModal({{ $order->id }})"
                                        class="flex items-center justify-center rounded-lg text-slate-400 dark:text-slate-500 p-1.5 hover:bg-gray-100 dark:hover:bg-white/[.06] hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none"
                                        title="{{ __('Lihat Detail') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <a
                                        href="{{ OrderResource::getUrl('edit', ['record' => $order]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold px-2.5 py-1.5 text-[11px] transition-all duration-150 focus:outline-none active:scale-[0.97]"
                                        title="{{ __('Kelola Order') }}"
                                    >
                                        <span>{{ __('Kelola') }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Cashier label on hover -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                <span class="text-[9px] text-slate-400 dark:text-slate-600 font-medium bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded shadow-sm border border-gray-100 dark:border-gray-800" title="{{ __('Kasir: :name', ['name' => optional($order->user)->name ?? __('Sistem')]) }}">
                                    {{ optional($order->user)->name ?? __('Sistem') }}
                                </span>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/20 p-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-700 mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Belum ada order dalam tampilan ini') }}</h3>
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-500 max-w-sm mx-auto leading-relaxed">
                                {{ __('Gunakan tombol "Tambah Order" di atas untuk mencatat transaksi baru, atau filter pencarian lain untuk mencari data.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($this->cardOrders->isNotEmpty() && $this->getFilteredTableQuery()->count() > $this->cardOrders->count())
                    <div class="rounded-xl bg-gray-50 dark:bg-white/[.02] border border-gray-100 dark:border-white/[.05] p-3.5 text-center text-xs font-medium text-slate-500 dark:text-slate-500">
                        {{ __('Menampilkan :count order terbaru. Gunakan List View untuk mengakses seluruh riwayat lengkap.', ['count' => $this->cardOrders->count()]) }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- Order Detail Modal — Thermal Receipt Style                        --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
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
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            id="{{ $modalId }}"
            class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-black/50 dark:bg-black/70 p-2 sm:p-6 backdrop-blur-sm overflow-y-auto"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                @click.away="close()"
                class="relative w-full max-w-md my-4 sm:my-auto font-mono"
            >
                {{-- Close button (floating outside receipt) --}}
                <button
                    type="button"
                    class="absolute -right-2 -top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white dark:bg-gray-800 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-white shadow-lg border border-gray-200 dark:border-gray-700 transition-colors"
                    x-on:click="close()"
                    aria-label="{{ __('Tutup') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Receipt Paper --}}
                <div class="bg-white dark:bg-gray-950 rounded-lg shadow-2xl dark:shadow-[0_25px_60px_rgba(0,0,0,0.6)] border border-gray-200/60 dark:border-gray-800 overflow-hidden">

                    {{-- ══ Receipt Header ══ --}}
                    <div class="px-6 pt-6 pb-4 text-center">
                        {{-- Store/Brand Icon --}}
                        <div class="flex justify-center mb-3">
                            <div class="h-10 w-10 rounded-full bg-amber-500 flex items-center justify-center shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                </svg>
                            </div>
                        </div>

                        {{-- Order Number --}}
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] font-bold mb-1">
                            {{ __('Order #:number', ['number' => $detailOrderMeta['order_number'] ?? '-']) }}
                        </p>

                        {{-- Customer Name --}}
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white font-sans tracking-tight">
                            {{ $detailOrderMeta['customer_name'] ?? 'Tamu' }}
                        </h2>

                        {{-- Status Badge --}}
                        <div class="flex justify-center mt-2">
                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $detailStatusStyle }}">
                                @if(($detailOrderMeta['status'] ?? '') === 'open')
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                    </span>
                                @endif
                                {{ $detailOrderMeta['status_label'] ?? __('orders.status.unknown') }}
                            </span>
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Meta Info (Date, Cashier, Type, Table) ══ --}}
                    <div class="px-6 py-3 text-[11px] text-slate-600 dark:text-slate-400 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-400 dark:text-slate-500">{{ __('Waktu') }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $detailOrderMeta['created_at'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 dark:text-slate-500">{{ __('Kasir') }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $detailOrderMeta['cashier_name'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 dark:text-slate-500">{{ __('Tipe') }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $detailOrderMeta['order_type_label'] ?? '-' }}
                                @if($detailOrderMeta['table_number'])
                                    · {{ __('Meja :number', ['number' => $detailOrderMeta['table_number']]) }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Order Items ══ --}}
                    <div class="px-6 py-3">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-[0.15em] font-bold mb-2 text-center">
                            {{ __('Daftar Pesanan') }}
                        </p>

                        <div class="space-y-0">
                            @forelse ($detailOrderItems as $item)
                                <div class="py-2 {{ !$loop->last ? 'border-b border-dotted border-gray-200 dark:border-gray-800' : '' }}">
                                    {{-- Item name row --}}
                                    <div class="flex justify-between items-start gap-2 text-xs">
                                        <span class="font-semibold text-slate-800 dark:text-white font-sans flex-1">{{ $item['name'] }}</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 shrink-0 tabular-nums">
                                            {{ Number::currency($item['subtotal'], 'IDR', locale: app()->getLocale()) }}
                                        </span>
                                    </div>
                                    {{-- Qty × Price detail --}}
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 tabular-nums">
                                        {{ $item['qty'] }} × {{ Number::currency($item['price'], 'IDR', locale: app()->getLocale()) }}
                                    </p>

                                    {{-- Toppings --}}
                                    @if (! empty($item['toppings']))
                                        <div class="mt-1.5 space-y-0.5 pl-3">
                                            @foreach ($item['toppings'] as $topping)
                                                <div class="flex justify-between text-[10px] text-slate-500 dark:text-slate-500 tabular-nums">
                                                    <span class="flex items-center gap-1">
                                                        <span class="text-amber-400">+</span>
                                                        <span>{{ $topping['name'] }}</span>
                                                    </span>
                                                    <span>{{ $topping['quantity'] }} × {{ Number::currency($topping['price'], 'IDR', locale: app()->getLocale()) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 dark:text-slate-600 italic py-3 text-center font-sans">{{ __('Belum ada item pada order ini.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- ── Dashed Separator ── --}}
                    <div class="mx-5 border-t border-dashed border-gray-300 dark:border-gray-700"></div>

                    {{-- ══ Payment Summary ══ --}}
                    <div class="px-6 py-3 space-y-1.5 text-xs tabular-nums">
                        <div class="flex justify-between text-slate-500 dark:text-slate-400">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ Number::currency($detailOrderMeta['subtotal_order'] ?? 0, 'IDR', locale: app()->getLocale()) }}</span>
                        </div>
                        @if (($detailOrderMeta['discount_order'] ?? 0) > 0)
                            <div class="flex justify-between text-rose-500 dark:text-rose-400">
                                <span>{{ __('Diskon') }}</span>
                                <span class="font-semibold">-{{ Number::currency($detailOrderMeta['discount_order'], 'IDR', locale: app()->getLocale()) }}</span>
                            </div>
                        @endif
                        @if (($detailOrderMeta['service_fee_order'] ?? 0) > 0)
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>{{ __('Biaya Layanan') }}</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ Number::currency($detailOrderMeta['service_fee_order'], 'IDR', locale: app()->getLocale()) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- ══ TOTAL ══ --}}
                    <div class="mx-5 border-t-2 border-double border-gray-400 dark:border-gray-600"></div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider font-sans">{{ __('Total') }}</span>
                        <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tabular-nums tracking-tight">
                            {{ Number::currency($detailOrderMeta['total_order'] ?? 0, 'IDR', locale: app()->getLocale()) }}
                        </span>
                    </div>
                    <div class="mx-5 border-t-2 border-double border-gray-400 dark:border-gray-600"></div>

                    {{-- ══ Thank You Footer ══ --}}
                    <div class="px-6 py-4 text-center">
                        <p class="text-[10px] text-slate-400 dark:text-slate-600 uppercase tracking-[0.2em] font-semibold">
                            {{ __('Terima Kasih') }}
                        </p>
                        <p class="text-[9px] text-slate-300 dark:text-slate-700 mt-1">
                            ★ ★ ★ ★ ★
                        </p>
                    </div>
                </div>

                {{-- ══ Action Buttons (below receipt) ══ --}}
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:justify-end font-sans">
                    <x-filament::button
                        color="gray"
                        x-on:click="close()"
                        size="lg"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Tutup') }}
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        color="primary"
                        :href="\App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $detailOrderId])"
                        size="lg"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Kelola Order') }}
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        color="info"
                        {{-- :href="''" --}}
                        size="lg"
                        class="hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        {{ __('Cetak') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
