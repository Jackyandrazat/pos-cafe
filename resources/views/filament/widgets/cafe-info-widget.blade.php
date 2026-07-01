<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-1">
            {{-- Left Section: Brand & Version info --}}
            <div class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    {{-- Coffee Cup Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v1.244m3-1.244v1.244m3-1.244v1.244M9 21h6m-6-3h6m3-6a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V9h12v3zm0 0h1.5a2.5 2.5 0 0 0 2.5-2.5V8.5a2.5 2.5 0 0 0-2.5-2.5H18v6z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ $cafeName }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Sistem Kasir Kuliner Pintar oleh <a href="https://www.kodeeweb.com" target="_blank" class="font-medium text-amber-600 hover:underline dark:text-amber-400">{{ $developer }}</a>
                    </p>
                </div>
            </div>

            {{-- Right Section: Version tag & Build info --}}
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 ring-1 ring-inset ring-amber-500/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                    Versi {{ $version }}
                </span>

                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                    Build {{ $buildDate }}
                </span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
