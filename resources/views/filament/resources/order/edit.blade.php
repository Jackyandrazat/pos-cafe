<x-filament::page>
    {{-- Formulir & tombol harus dibungkus dalam form --}}
    <form wire:submit.prevent="save">
        {{-- Form --}}
        {{ $this->form }}

        {{-- Komponen Livewire custom --}}
        @livewire('order-item-builder', ['orderId' => $record->id])


        {{-- Tombol-tombol bawaan --}}
        <x-filament::actions :actions="$this->getCachedFormActions()" class="mt-6" />
    </form>
</x-filament::page>
