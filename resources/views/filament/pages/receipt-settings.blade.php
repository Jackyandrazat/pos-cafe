<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Formulir Pengaturan --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-6">
            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle" color="primary">
                        {{ __('Simpan Pengaturan Struk') }}
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Panel Preview Simulasi Struk Thermal --}}
        <div class="lg:col-span-5 xl:col-span-4 sticky top-6">
            <div class="rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white dark:bg-gray-900 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <x-filament::icon icon="heroicon-o-eye" class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Simulasi Struk') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Tampilan pada kertas thermal 58mm') }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ $data['receipt_paper_width'] ?? '58mm' }}
                    </span>
                </div>

                {{-- Simulasi Kertas Thermal --}}
                <div class="mx-auto max-w-[280px] bg-amber-50/40 dark:bg-gray-950 p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 font-mono text-xs text-gray-900 dark:text-gray-100 shadow-inner select-none leading-relaxed">
                    {{-- Header --}}
                    <div class="text-center space-y-1">
                        <h4 class="font-black text-sm tracking-wide uppercase">
                            {{ $data['receipt_cafe_name'] ?: 'NAMA KAFE ANDA' }}
                        </h4>
                        @if (!empty($data['receipt_cafe_address']))
                            <p class="text-[10px] text-gray-600 dark:text-gray-300">
                                {{ $data['receipt_cafe_address'] }}
                            </p>
                        @endif
                        @if (!empty($data['receipt_cafe_phone']))
                            <p class="text-[10px] text-gray-600 dark:text-gray-300">
                                Telp/WA: {{ $data['receipt_cafe_phone'] }}
                            </p>
                        @endif
                        <div class="border-t border-dashed border-gray-400 dark:border-gray-600 my-2"></div>
                    </div>

                    {{-- Sample Metadata --}}
                    <div class="space-y-0.5 text-[11px]">
                        <div class="flex justify-between">
                            <span>No. Struk :</span>
                            <span class="font-bold">#ORD-9982</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tanggal   :</span>
                            <span>{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir     :</span>
                            <span>Kasir POS</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Meja      :</span>
                            <span class="font-bold">MEJA 04</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-400 dark:border-gray-600 my-2"></div>

                    {{-- Sample Items --}}
                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between font-semibold">
                            <span>1x Cappuccino</span>
                            <span>35.000</span>
                        </div>
                        <div class="text-[10px] text-gray-500 pl-3">
                            + Oat Milk, Extra Shot
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>1x Butter Croissant</span>
                            <span>25.000</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-400 dark:border-gray-600 my-2"></div>

                    {{-- Totals --}}
                    <div class="space-y-0.5 text-[11px]">
                        <div class="flex justify-between">
                            <span>Subtotal  :</span>
                            <span>60.000</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-1 border-t border-gray-300 dark:border-gray-700">
                            <span>TOTAL     :</span>
                            <span>Rp 60.000</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Metode    :</span>
                            <span class="font-semibold">QRIS</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-400 dark:border-gray-600 my-2.5"></div>

                    {{-- Footer --}}
                    <div class="text-center space-y-1 text-[10px]">
                        @if (!empty($data['receipt_footer_text']))
                            <p class="font-bold">
                                {{ $data['receipt_footer_text'] }}
                            </p>
                        @endif

                        @if (!empty($data['receipt_wifi_ssid']))
                            <p class="text-gray-700 dark:text-gray-300">
                                WiFi: <span class="font-semibold">{{ $data['receipt_wifi_ssid'] }}</span>
                                @if (!empty($data['receipt_wifi_password']))
                                    | Pass: <span class="font-semibold">{{ $data['receipt_wifi_password'] }}</span>
                                @endif
                            </p>
                        @endif

                        @if (!empty($data['receipt_feedback_info']))
                            <p class="text-gray-600 dark:text-gray-400">
                                Kritik & Saran: {{ $data['receipt_feedback_info'] }}
                            </p>
                        @endif

                        @if (!empty($data['receipt_footer_subtext']))
                            <p class="text-[9px] text-gray-500 pt-1">
                                {{ $data['receipt_footer_subtext'] }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
