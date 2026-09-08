<x-filament::page>
    {{-- Formulir & tombol harus dibungkus dalam form --}}
    <form wire:submit.prevent="create" class="flex flex-col gap-y-6">
        {{-- Form --}}
        {{ $this->form }}

        {{-- Komponen Livewire custom --}}
        <div>
            @livewire('order-item-builder')
        </div>

        {{-- Tombol-tombol bawaan --}}
        <x-filament::actions :actions="$this->getCachedFormActions()" />
    </form>
</x-filament::page>
