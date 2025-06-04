<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-2">Bahan Baku Kadaluarsa</h2>

        @forelse ($expiredIngredients as $item)
            <div class="text-red-600">
                {{ $item->name }} - {{ \Carbon\Carbon::parse($item->expired)->format('d M Y') }}
            </div>
        @empty
            <div class="text-sm text-gray-500">Tidak ada yang kadaluarsa.</div>
        @endforelse

        <hr class="my-4" />

        <h2 class="text-lg font-bold mb-2">Akan Kadaluarsa (7 Hari)</h2>

        @forelse ($soonExpiredIngredients as $item)
            <div class="text-yellow-600">
                {{ $item->name }} - {{ \Carbon\Carbon::parse($item->expired)->format('d M Y') }}
            </div>
        @empty
            <div class="text-sm text-gray-500">Tidak ada bahan mendekati expired.</div>
        @endforelse
    </x-filament::card>
</x-filament::widget>
