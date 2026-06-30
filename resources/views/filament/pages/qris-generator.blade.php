<x-filament::page>
    {{-- Status Banner --}}
    @if ($this->isSaved)
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 px-4 py-3">
            <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0" />
            <p class="text-sm text-green-700 dark:text-green-300">
                <span class="font-semibold">QRIS Aktif.</span>
                Pembayaran QRIS akan otomatis menghasilkan QR code dinamis sesuai nominal transaksi.
            </p>
        </div>
    @else
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700 px-4 py-3">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0" />
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                <span class="font-semibold">QRIS belum dikonfigurasi.</span>
                Tambahkan Static QRIS string di bawah agar sistem bisa otomatis generate QR per transaksi.
            </p>
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check" color="success">
                Simpan Konfigurasi
            </x-filament::button>

            <x-filament::button wire:click="previewQr" type="button" icon="heroicon-o-qr-code" color="info">
                Preview QR
            </x-filament::button>

            @if ($this->previewQrSvg)
                <x-filament::button wire:click="resetPreview" type="button" icon="heroicon-o-arrow-path" color="gray">
                    Reset Preview
                </x-filament::button>
            @endif
        </div>
    </form>

    {{-- Preview QR Result --}}
    @if ($this->previewQrSvg)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Preview QR Code</x-slot>
            <x-slot name="description">
                QR dinamis untuk transaksi Rp {{ number_format($this->previewAmount, 0, ',', '.') }} — siap scan
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- QR Code Display --}}
                <div class="flex flex-col items-center justify-center p-6 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        {!! $this->previewQrSvg !!}
                    </div>

                    <p class="mt-4 text-lg font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($this->previewAmount, 0, ',', '.') }}
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Scan menggunakan aplikasi e-wallet atau mobile banking
                    </p>
                </div>

                {{-- QRIS String + Info --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Dynamic QRIS String
                        </label>
                        <textarea
                            readonly
                            rows="4"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-xs font-mono text-gray-800 dark:text-gray-200"
                        >{{ $this->previewQrisStr }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-3 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500">Tag 54 (Amount)</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-1">
                                Rp {{ number_format($this->previewAmount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-3 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500">CRC-16 Checksum</p>
                            <p class="text-sm font-mono font-semibold text-gray-800 dark:text-gray-200 mt-1">
                                {{ strtoupper(substr($this->previewQrisStr, -4)) }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 p-3">
                        <div class="flex gap-2">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0 mt-0.5" />
                            <div class="text-xs text-primary-700 dark:text-primary-300">
                                <p class="font-semibold">Cara kerja otomatis:</p>
                                <ul class="mt-1 space-y-0.5 list-disc list-inside">
                                    <li>Static string disimpan di database</li>
                                    <li>Saat payment QRIS dibuat → Tag 54 di-inject otomatis</li>
                                    <li>CRC-16/CCITT-FALSE dihitung ulang (poly 0x1021)</li>
                                    <li>QR SVG disimpan di <code>payment.meta</code></li>
                                    <li>Tidak ada biaya gateway eksternal 🎉</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament::page>
