<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @php($stats = $this->overallStats)

        <x-filament::section>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Penjualan</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        Rp {{ number_format($stats['total_sales'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Transaksi</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        {{ number_format($stats['transactions'] ?? 0) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata Transaksi</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        Rp {{ number_format($stats['average'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode dipilih</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $this->currentRangeLabel }}</p>
                </div>

                @if (count($this->cashierSummary))
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($this->cashierSummary as $summary)
                            <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kasir</p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-gray-50">
                                            {{ $summary['name'] }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaksi</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                                            {{ number_format($summary['transactions']) }}
                                        </p>
                                    </div>
                                </div>
                                <p class="mt-3 text-2xl font-bold text-primary-600 dark:text-primary-400">
                                    Rp {{ number_format($summary['total'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada rekap kasir pada periode ini.</p>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
