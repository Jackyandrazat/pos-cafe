@php
    use App\Filament\Resources\OrderResource;
    use App\Filament\Resources\PaymentResource;
    use Illuminate\Support\Number;

    $paymentStatusStyles = [
        'pending' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
        'captured' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
        'failed' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300',
        'expired' => 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-500/30 dark:bg-gray-500/10 dark:text-gray-300',
        'refunded' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300',
    ];

    $orderStatusStyles = [
        'open' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
        'cancelled' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300',
    ];

    $statusAccent = [
        'pending' => 'border-l-amber-500 dark:border-l-amber-500',
        'captured' => 'border-l-emerald-500 dark:border-l-emerald-500',
        'failed' => 'border-l-rose-500 dark:border-l-rose-500',
        'expired' => 'border-l-gray-400 dark:border-l-gray-500',
        'refunded' => 'border-l-blue-500 dark:border-l-blue-500',
    ];

    $paymentMethodIcons = [
        'cash' => '',
        'qris' => '',
        'ewallet' => '',
        'transfer' => '',
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                {{ __('Pilih tampilan daftar pembayaran yang paling nyaman untuk workflow Anda.') }}
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
                            {{ __('Filter dan cari pembayaran di tampilan kartu. Ketik nama pelanggan, nomor order, menu, atau metode bayar.') }}
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
                            placeholder="{{ __('Cari pembayaran...') }}"
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
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse ($this->cardPayments as $payment)
                        @php
                            $order = $payment->order;
                            $menuItems = $order?->order_items ?? collect();
                            $menuCount = $menuItems->count();
                            $previewLimit = 2;
                            $previewItems = $menuCount > $previewLimit ? $menuItems->take($previewLimit) : $menuItems;
                            $accent = $statusAccent[$payment->status->value ?? $payment->status] ?? 'border-l-gray-300 dark:border-l-gray-600';
                        @endphp

                        <article
                            class="group relative flex h-full flex-col rounded-xl border border-gray-200/70 dark:border-white/[.06] border-l-[3px] {{ $accent }} bg-white dark:bg-gray-900 p-4 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-[0_8px_24px_rgba(0,0,0,0.3)] hover:-translate-y-0.5 transition-all duration-200 ease-out overflow-hidden"
                        >
                            <!-- Header Section -->
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="min-w-0 font-sans">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ __('Payment #:number', ['number' => $payment->id]) }}
                                    </p>
                                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5 leading-snug truncate" title="{{ $order?->customer_name ?: __('Tamu') }}">
                                        {{ $order?->customer_name ?: __('Tamu') }}
                                    </h2>
                                    <div class="flex flex-wrap items-center gap-1 mt-1 text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                        <span>{{ __('Order #:number', ['number' => $order?->id ?? '—']) }}</span>
                                        <span>·</span>
                                        <span>{{ optional($payment->created_at)->timezone(config('app.timezone'))->translatedFormat('d M Y • H:i') }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-1.5 shrink-0">
                                    <!-- Payment Status Badge -->
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                        $paymentStatusStyles[$payment->status->value ?? $payment->status] ?? 'border-gray-200 bg-gray-100 text-gray-700 dark:border-white/20 dark:bg-white/10 dark:text-white/80',
                                    ])>
                                        @if(($payment->status->value ?? $payment->status) === 'pending')
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                            </span>
                                        @elseif(($payment->status->value ?? $payment->status) === 'captured')
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                            </span>
                                        @endif
                                        {{ $payment->status instanceof \App\Enums\PaymentStatus ? $payment->status->label() : (\App\Enums\PaymentStatus::tryFrom($payment->status)?->label() ?? $payment->status) }}
                                    </span>

                                    <!-- Order Status Badge -->
                                    @if($order)
                                        <span @class([
                                            'inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-semibold border',
                                            $orderStatusStyles[$order->status] ?? 'border-gray-200 bg-gray-50 text-gray-650 dark:border-white/10 dark:bg-white/5 dark:text-white/60',
                                        ])>
                                            {{ __('orders.status.' . ($order->status ?? 'unknown')) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Details / Badges -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-3 font-sans">
                                <!-- Payment Method -->
                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 dark:bg-white/5 text-slate-700 dark:text-slate-350 px-2 py-0.5 text-[10px] font-bold">
                                    <span>{{ $paymentMethodIcons[$payment->payment_method] ?? '💵' }}</span>
                                    <span>{{ str($payment->payment_method ?? 'unknown')->headline() }}</span>
                                    @if($payment->payment_channel)
                                        <span class="text-slate-400 dark:text-slate-500 font-medium">( {{ strtoupper($payment->payment_channel) }} )</span>
                                    @endif
                                </span>

                                <!-- Order Type Badge -->
                                @if($order)
                                    @php
                                        $orderTypeBadge = [
                                            'dine_in' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
                                            'take_away' => 'bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400',
                                            'delivery' => 'bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400',
                                        ];
                                        $typeBadgeClass = $orderTypeBadge[$order->order_type] ?? 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $typeBadgeClass }}">
                                        {{ $order->order_type ? $this->getOrderTypeLabel($order->order_type) : '—' }}
                                    </span>

                                    @if(optional($order->table)->table_number)
                                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 text-[10px] font-semibold">
                                            {{ __('Meja :number', ['number' => optional($order->table)->table_number]) }}
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <!-- Financial Summary (Receipt Box) -->
                            <div class="rounded-xl border border-gray-100 dark:border-white/[.04] bg-gray-50/50 dark:bg-white/[.01] p-3 text-xs space-y-1.5 font-mono mb-3 shadow-inner">
                                <div class="flex justify-between text-slate-500 dark:text-slate-400 text-[11px]">
                                    <span>{{ __('Total Tagihan') }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ Number::currency($order->total_order ?? $order->total ?? 0, 'IDR', locale: app()->getLocale()) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-slate-700 dark:text-slate-350 border-t border-dotted border-gray-250 dark:border-white/10 pt-1.5">
                                    <span>{{ __('Jumlah Bayar') }}</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">
                                        {{ Number::currency($payment->amount_paid ?? 0, 'IDR', locale: app()->getLocale()) }}
                                    </span>
                                </div>
                                @if(($payment->change_return ?? 0) > 0)
                                    <div class="flex justify-between text-emerald-600 dark:text-emerald-450 font-bold border-t border-dotted border-gray-200 dark:border-white/5 pt-1.5">
                                        <span>{{ __('Kembalian') }}</span>
                                        <span>
                                            {{ Number::currency($payment->change_return ?? 0, 'IDR', locale: app()->getLocale()) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="flex justify-between text-slate-400 dark:text-slate-500 text-[11px] border-t border-dotted border-gray-200 dark:border-white/5 pt-1.5">
                                        <span>{{ __('Kembalian') }}</span>
                                        <span>IDR 0.00</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Menu Items Summary -->
                            <div x-data="{ expanded: false }" class="flex-1 mb-3 font-sans">
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
                                            <li class="flex items-center justify-between rounded-lg px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-400 bg-gray-50/40 dark:bg-white/[.01] hover:bg-gray-100/50 dark:hover:bg-white/[.03] transition-colors">
                                                <span class="flex-1 truncate pr-2 font-medium text-slate-700 dark:text-slate-350">
                                                    {{ $item->product->name ?? $item->product_name ?? __('Menu #:number', ['number' => $item->id]) }}
                                                </span>
                                                <span class="font-mono text-slate-400 dark:text-slate-500 text-[10px]">×{{ $item->qty ?? 0 }}</span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($menuCount > $previewLimit)
                                        <ul x-show="expanded" x-cloak class="space-y-0.5 max-h-36 overflow-y-auto pr-1">
                                            @foreach ($menuItems as $item)
                                                <li class="flex items-center justify-between rounded-lg px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-400 bg-gray-50/40 dark:bg-white/[.01] hover:bg-gray-100/50 dark:hover:bg-white/[.03] transition-colors">
                                                    <span class="flex-1 truncate pr-2 font-medium text-slate-700 dark:text-slate-350">
                                                        {{ $item->product->name ?? $item->product_name ?? __('Menu #:number', ['number' => $item->id]) }}
                                                    </span>
                                                    <span class="font-mono text-slate-400 dark:text-slate-500 text-[10px]">×{{ $item->qty ?? 0 }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @else
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 italic py-1">{{ __('Belum ada menu untuk order ini.') }}</p>
                                @endif
                            </div>

                            <!-- Footer Section / Actions -->
                            <div class="mt-auto border-t border-gray-100 dark:border-white/[.04] pt-3 font-sans">
                                <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 font-medium mb-3">
                                    <span>{{ __('Diperbarui :time', ['time' => optional($payment->updated_at)->diffForHumans() ?? '-']) }}</span>
                                    @if ($payment->confirmedBy)
                                        <span class="bg-gray-50 dark:bg-white/5 px-1.5 py-0.5 rounded font-semibold">{{ __('Oleh: :name', ['name' => $payment->confirmedBy->name]) }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-end gap-2">
                                    @if($order)
                                        <x-filament::button
                                            tag="a"
                                            href="{{ \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order]) }}"
                                            color="gray"
                                            size="sm"
                                            class="rounded-lg font-bold"
                                        >
                                            <span>{{ __('Kelola Order') }}</span>
                                        </x-filament::button>
                                    @endif


                                    <x-filament::button
                                        tag="a"
                                        :href="PaymentResource::getUrl('edit', ['record' => $payment])"
                                        color="warning"
                                        size="sm"
                                        class="rounded-lg font-bold"
                                    >
                                        <span>{{ __('Kelola Pembayaran') }}</span>
                                    </x-filament::button>

                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/20 p-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-700 mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Belum ada pembayaran cocok') }}</h3>
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-500 max-w-sm mx-auto leading-relaxed">
                                {{ __('Coba ubah kata kunci pencarian atau beralih ke List View untuk melihat data lengkap.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($this->cardPayments->isNotEmpty() && $this->getFilteredTableQuery()->count() > $this->cardPayments->count())
                    <div class="rounded-xl bg-gray-50 dark:bg-white/[.02] border border-gray-100 dark:border-white/[.05] p-3.5 text-center text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ __('Menampilkan :count pembayaran terbaru. Gunakan List View untuk riwayat lengkap.', ['count' => $this->cardPayments->count()]) }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
