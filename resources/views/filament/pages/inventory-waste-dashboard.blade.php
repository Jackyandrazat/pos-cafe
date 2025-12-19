<x-filament::page>
    <form wire:submit.prevent="applyFilters" class="space-y-4">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit">
                Terapkan
            </x-filament::button>

            <x-filament::button type="button" color="gray" wire:click="exportCsv">
                Export CSV
            </x-filament::button>
        </div>
    </form>

    <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 border rounded-xl bg-white shadow-sm">
            <p class="text-sm text-gray-500">Total Stok Masuk</p>
            <p class="text-2xl font-semibold">{{ number_format($summary['total_stock_in'] ?? 0, 2) }}</p>
        </div>
        <div class="p-4 border rounded-xl bg-white shadow-sm">
            <p class="text-sm text-gray-500">Total Pemakaian</p>
            <p class="text-2xl font-semibold">{{ number_format($summary['total_usage'] ?? 0, 2) }}</p>
        </div>
        <div class="p-4 border rounded-xl bg-white shadow-sm">
            <p class="text-sm text-gray-500">Total Waste</p>
            <p class="text-2xl font-semibold text-rose-600">{{ number_format($summary['total_waste'] ?? 0, 2) }}</p>
        </div>
        <div class="p-4 border rounded-xl bg-white shadow-sm">
            <p class="text-sm text-gray-500">Biaya Waste / %</p>
            <p class="text-2xl font-semibold">Rp{{ number_format($summary['total_waste_cost'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">Waste {{ number_format($summary['waste_ratio'] ?? 0, 2) }}%</p>
        </div>
    </div>

    <div class="mt-6 overflow-auto border rounded-xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Bahan</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Stok Masuk</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Pemakaian</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Waste</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Konsumsi</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Variance</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Stok Saat Ini</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Biaya Waste</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">% Waste</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">
                            {{ $row['name'] }}
                            <div class="text-xs text-gray-500">Satuan: {{ $row['unit'] ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['stock_in'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['usage'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-rose-600">{{ number_format($row['waste'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['consumption'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['variance'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['current_stock'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">Rp{{ number_format($row['waste_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($row['waste_ratio'], 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-sm text-gray-500">
                            Belum ada data pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::page>
