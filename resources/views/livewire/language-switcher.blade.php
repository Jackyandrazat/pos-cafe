<x-filament::dropdown placement="bottom-end" teleport>
    <x-slot name="trigger">
        <button
            type="button"
            class="flex items-center gap-1 rounded-2xl border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/70 transition hover:text-white"
        >
            <span>{{ strtoupper($locale) }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
            </svg>
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach ($locales as $code => $label)
            <x-filament::dropdown.list.item
                wire:click="switchLocale('{{ $code }}')"
                :color="$locale === $code ? 'primary' : 'gray'"
            >
                <span class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide">{{ strtoupper($code) }}</span>
                    <span>{{ __($label) }}</span>
                </span>
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
