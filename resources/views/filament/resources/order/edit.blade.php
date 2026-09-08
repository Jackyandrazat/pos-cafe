<x-filament::page>
    {{-- Formulir & tombol harus dibungkus dalam form --}}
    <form wire:submit.prevent="save" class="flex flex-col gap-y-6">
        {{-- Form --}}
        {{ $this->form }}

        {{-- Komponen Livewire custom --}}
        <div>
            @livewire('order-item-builder', ['orderId' => $record->id])
        </div>

        {{-- Tombol-tombol bawaan --}}
        <x-filament::actions :actions="$this->getCachedFormActions()" />
    </form>
</x-filament::page>
