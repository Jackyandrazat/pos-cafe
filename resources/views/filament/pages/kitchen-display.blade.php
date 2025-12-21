<x-filament::page>
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <x-filament::button color="gray" :disabled="$statusFilter === 'active'" wire:click="setFilter('active')">Aktif</x-filament::button>
            <x-filament::button color="warning" :disabled="$statusFilter === 'ready'" wire:click="setFilter('ready')">Siap</x-filament::button>
            <x-filament::button color="success" :disabled="$statusFilter === 'completed'" wire:click="setFilter('completed')">Selesai</x-filament::button>
        </div>
        <x-filament::button color="gray" wire:click="refreshOrders">Refresh</x-filament::button>
    </div>

    <div wire:poll.15s="refreshOrders" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($orders as $order)
            <div class="bg-white border rounded-xl shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Order #{{ $order['id'] }}</p>
                        <p class="text-lg font-semibold capitalize">{{ str_replace('_', ' ', $order['status']) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ $order['created_at'] }}</p>
                        <p class="text-sm text-gray-600">{{ $order['table'] ? 'Meja '.$order['table'] : strtoupper($order['order_type']) }}</p>
                    </div>
                </div>

                <ul class="space-y-1">
                    @foreach ($order['items'] as $item)
                        <li class="text-sm">
                            <span class="font-semibold">{{ $item['qty'] }}x</span>
                            {{ $item['name'] }}
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-wrap gap-2 pt-2">
                    @php
                        $status = $order['status'];
                    @endphp
                    @if ($status !== 'preparing')
                        <x-filament::button size="sm" color="warning" wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Preparing->value }}')">
                            Mulai Masak
                        </x-filament::button>
                    @endif
                    @if (! in_array($status, ['ready', 'completed']))
                        <x-filament::button size="sm" color="success" wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Ready->value }}')">
                            Tandai Siap
                        </x-filament::button>
                    @endif
                    @if ($status !== 'completed')
                        <x-filament::button size="sm" color="gray" wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Completed->value }}')">
                            Selesai
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-sm text-gray-500 py-8">
                Tidak ada pesanan pada tampilan ini.
            </div>
        @endforelse
    </div>
</x-filament::page>
