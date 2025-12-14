@php
    use App\Filament\Resources\OrderResource;
    use App\Filament\Resources\PaymentResource;
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
                {{ __('Pilih tampilan daftar pembayaran yang paling nyaman untuk workflow Anda.') }}
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
                        {{ __('Filter dan cari pembayaran di tampilan kartu. Ketik nama pelanggan, nomor order, menu, atau metode bayar.') }}
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
                            placeholder="{{ __('Cari pembayaran...') }}"
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
                    @forelse ($this->cardPayments as $payment)
                        @php
                            $order = $payment->order;
                            $menuItems = $order?->order_items ?? collect();
                            $menuCount = $menuItems->count();
                            $previewLimit = 3;
                            $previewItems = $menuCount > $previewLimit ? $menuItems->take($previewLimit) : $menuItems;
                        @endphp

                        <article class="flex h-full flex-col gap-6 rounded-3xl bg-slate-950/80 p-6 shadow-[0_25px_60px_rgba(2,6,23,0.55)] ring-1 ring-white/5 transition hover:ring-amber-400/40">
                            <header class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/45">{{ __('Payment #:number', ['number' => $payment->id]) }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold text-white">
                                        {{ $order?->customer_name ?: __('Tamu') }} • {{ __('Order #:number', ['number' => $order?->id ?? '—']) }}
                                    </h2>
                                </div>

                                <span @class([
                                    'inline-flex items-center rounded-full border px-4 py-1 text-sm font-semibold uppercase tracking-wide',
                                    $statusStyles[$order?->status] ?? 'border-white/20 bg-white/10 text-white/80',
                                ])>
                                    {{ __('orders.status.' . ($order?->status ?? 'unknown')) }}
                                </span>
                            </header>

                            <dl class="grid gap-4 text-sm text-white/70 sm:grid-cols-2">
                                <div class="flex flex-col gap-1 rounded-2xl border border-white/5 bg-white/5 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Metode Bayar') }}</dt>
                                    <dd class="text-base font-semibold text-white">
                                        {{ str($payment->payment_method ?? 'unknown')->headline() }}
                                    </dd>
                                    <dd class="text-sm text-white/60">
                                        {{ optional($order?->table)->table_number ? __('Meja :number', ['number' => optional($order?->table)->table_number]) : __('Tanpa meja') }} • {{ $order?->order_type ? $this->getOrderTypeLabel($order->order_type) : '—' }}
                                    </dd>
                                </div>

                                <div class="flex flex-col gap-1 rounded-2xl border border-white/5 bg-white/5 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Total Bayar') }}</dt>
                                    <dd class="text-3xl font-bold text-emerald-300">
                                        {{ Number::currency($payment->amount_paid ?? 0, 'IDR', locale: app()->getLocale()) }}
                                    </dd>
                                    <dd class="text-sm text-white/60">
                                        {{ __('Kembalian: :amount', ['amount' => Number::currency($payment->change_return ?? 0, 'IDR', locale: app()->getLocale())]) }}
                                    </dd>
                                </div>

                                <div class="flex flex-col gap-1 rounded-2xl border border-white/5 bg-white/5 p-4 sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Ringkasan Menu') }}</dt>
                                    <dd x-data="{ expanded: false }" class="flex flex-col gap-3 text-sm text-white/60">
                                        <div class="flex items-center justify-between gap-2">
                                            <span>{{ trans_choice('orders.menu_count', $menuCount, ['count' => $menuCount]) }}</span>

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
                                                    <li class="flex items-center justify-between text-xs font-medium sm:text-sm">
                                                        <span class="flex-1 truncate pr-2">{{ $item->product->name ?? $item->product_name ?? __('Menu #:number', ['number' => $item->id]) }}</span>
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
                                                    <h4 class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200">{{ __('Daftar Menu Lengkap') }}</h4>
                                                    <ul class="mt-3 space-y-1 text-xs font-medium sm:text-sm">
                                                        @foreach ($menuItems as $item)
                                                            <li class="flex items-center justify-between">
                                                                <span class="flex-1 truncate pr-2">{{ $item->product->name ?? $item->product_name ?? __('Menu #:number', ['number' => $item->id]) }}</span>
                                                                <span class="text-white/40">×{{ $item->qty ?? 0 }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-white/40">{{ __('Belum ada menu untuk order ini.') }}</span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="rounded-2xl border border-white/5 bg-white/5 p-4 sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ __('Waktu Pembayaran') }}</dt>
                                    <dd class="mt-2 text-base font-semibold text-white">
                                        {{ optional($payment->created_at)->timezone(config('app.timezone'))->translatedFormat('d M Y • H:i') }}
                                    </dd>
                                    <dd class="text-sm text-white/60">
                                        {{ __('Terakhir diperbarui :time', ['time' => optional($payment->updated_at)->diffForHumans() ?? '-']) }}
                                    </dd>
                                </div>
                            </dl>

                            <footer class="flex flex-col gap-3 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
                                <span>
                                    {{ __('Total tagihan order: :amount', ['amount' => Number::currency($order->total_order ?? $order->total ?? 0, 'IDR', locale: app()->getLocale())]) }}
                                </span>

                                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                    <x-filament::button
                                        tag="a"
                                        size="lg"
                                        color="gray"
                                        class="w-full sm:w-auto"
                                        :href="\App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order])"
                                        :disabled="blank($order)"
                                    >
                                        {{ __('Kelola Order') }}
                                    </x-filament::button>

                                    <x-filament::button
                                        tag="a"
                                        size="lg"
                                        color="primary"
                                        class="w-full sm:w-auto"
                                        :href="PaymentResource::getUrl('edit', ['record' => $payment])"
                                    >
                                        {{ __('Kelola Pembayaran') }}
                                    </x-filament::button>
                                </div>
                            </footer>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-white/15 bg-white/5 p-8 text-center text-white/70">
                            <h3 class="text-lg font-semibold text-white">{{ __('Belum ada pembayaran cocok') }}</h3>
                            <p class="mt-2 text-sm text-white/60">
                                {{ __('Coba ubah kata kunci pencarian atau beralih ke List View untuk melihat data lengkap.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($this->cardPayments->isNotEmpty() && $this->getFilteredTableQuery()->count() > $this->cardPayments->count())
                    <p class="text-center text-sm text-white/60">
                        {{ __('Menampilkan :count pembayaran terbaru. Gunakan List View untuk riwayat lengkap.', ['count' => $this->cardPayments->count()]) }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
