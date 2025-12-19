<x-filament::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-full max-w-xs space-y-1">
                <label class="text-sm font-medium text-gray-700">Area</label>
                <select wire:model.live="selectedArea" class="fi-input block w-full rounded-lg border-gray-300">
                    @foreach ($this->areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-filament::button wire:click="refreshData" color="gray">Refresh</x-filament::button>
            <x-filament::button tag="a" href="{{ \App\Filament\Resources\TableQueueEntryResource::getUrl() }}" color="primary">
                Kelola Antrean
            </x-filament::button>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($tables as $table)
                        <div class="border rounded-xl p-4 shadow-sm space-y-2" wire:key="table-card-{{ $table['id'] }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Meja</p>
                                    <p class="text-xl font-semibold">{{ $table['table_number'] }}</p>
                                </div>
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-semibold text-white',
                                    'bg-emerald-500' => $table['status'] === 'available',
                                    'bg-amber-500' => $table['status'] === 'reserved',
                                    'bg-rose-500' => $table['status'] === 'occupied',
                                    'bg-gray-500' => $table['status'] === 'cleaning',
                                ])>
                                    {{ ucfirst($table['status']) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">Kapasitas: {{ $table['capacity'] ?? 0 }} pax</p>
                            @if ($table['notes'])
                                <p class="text-xs text-gray-400">{{ $table['notes'] }}</p>
                            @endif
                            <div class="flex flex-wrap gap-2 pt-2">
                                @foreach ($statusOptions as $key => $label)
                                    <x-filament::button size="xs"
                                        color="{{ $key === 'available' ? 'success' : ($key === 'occupied' ? 'danger' : ($key === 'reserved' ? 'warning' : 'gray')) }}"
                                        :disabled="$table['status'] === $key"
                                        wire:click.prevent="setStatus({{ $table['id'] }}, '{{ $key }}')">
                                        {{ $label }}
                                        @if ($table['status'] === $key)
                                            <x-filament::icon icon="heroicon-m-check" class="w-3 h-3"/>
                                        @endif
                                    </x-filament::button>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-sm text-gray-500">Belum ada meja terdaftar pada area ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Antrean</h3>
                    <a href="{{ \App\Filament\Resources\TableQueueEntryResource::getUrl('create') }}" class="text-sm text-primary-600">Tambah</a>
                </div>

                <div class="space-y-3">
                    @forelse ($queueEntries as $entry)
                        <div class="border rounded-lg p-4 shadow-sm space-y-2" wire:key="queue-entry-{{ $entry['id'] }}">
                            <div class="flex justify-between text-sm font-semibold">
                                <span>{{ $entry['guest_name'] }} ({{ $entry['party_size'] }} pax)</span>
                                <span class="text-gray-500">{{ ucfirst($entry['status']) }}</span>
                            </div>
                            <p class="text-xs text-gray-500">Masuk: {{ $entry['check_in_at'] ? \Carbon\Carbon::parse($entry['check_in_at'])->format('H:i') : '-' }}</p>

                            <div class="space-y-2">
                                <select wire:model="assignments.{{ $entry['id'] }}" class="fi-input block w-full rounded-lg border-gray-300">
                                    <option value="">Pilih Meja</option>
                                    @foreach ($this->availableTables as $table)
                                        <option value="{{ $table->id }}">Meja {{ $table->table_number }} ({{ $table->capacity }} pax)</option>
                                    @endforeach
                                </select>

                                <div class="flex flex-wrap gap-2">
                                    <x-filament::button size="sm" wire:click.prevent="seatQueueEntry({{ $entry['id'] }})" color="primary">
                                        Seat
                                    </x-filament::button>
                                    <x-filament::button size="sm" wire:click.prevent="callQueueEntry({{ $entry['id'] }})" color="warning">
                                        Call
                                    </x-filament::button>
                                    <x-filament::button size="sm" wire:click.prevent="cancelQueueEntry({{ $entry['id'] }})" color="gray">
                                        Cancel
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada antrean saat ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
