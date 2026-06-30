@php
    $meta    = $payment->meta ?? [];
    $method  = $payment->payment_method;
    $channel = $payment->payment_channel;

    $channelLabel = match($method) {
        'ewallet'  => strtoupper($channel ?? 'E-Wallet'),
        'transfer' => strtoupper($channel ?? 'Bank'),
        'qris'     => 'QRIS',
        default    => strtoupper($method),
    };
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Metode Pembayaran</p>
            <p class="text-base font-bold text-gray-900 dark:text-white">{{ $channelLabel }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Provider</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $payment->provider ?? '-' }}</p>
        </div>
    </div>

    {{-- QRIS dengan QR Code SVG --}}
    @if ($method === 'qris')
        @if (! empty($meta['qr_svg']))
            {{-- QR code tersedia — tampilkan SVG inline --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 space-y-3">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">📱 Scan QRIS untuk Membayar</p>

                {{-- QR Code --}}
                <div class="flex justify-center">
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-blue-100">
                        {!! $meta['qr_svg'] !!}
                    </div>
                </div>

                <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                    {{ $meta['note'] ?? 'Scan menggunakan aplikasi e-wallet atau mobile banking Anda.' }}
                </p>

                {{-- QRIS String (untuk kasir / debug) --}}
                @if (! empty($meta['qris_string']))
                    <details class="text-xs">
                        <summary class="cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            Lihat QRIS String
                        </summary>
                        <pre class="mt-2 p-2 bg-white dark:bg-gray-800 rounded text-xs overflow-x-auto border border-blue-200 break-all whitespace-pre-wrap">{{ $meta['qris_string'] }}</pre>
                    </details>
                @endif
            </div>
        @else
            {{-- Fallback: QRIS manual tanpa QR generate --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase">📱 QRIS Manual</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                    {{ $meta['note'] ?? 'Minta pelanggan scan QRIS merchant, lalu konfirmasi ke kasir.' }}
                </p>
                @if (! empty($meta['error']))
                    <p class="text-xs text-red-500 mt-1">⚠ {{ $meta['error'] }}</p>
                @endif
            </div>
        @endif
    @endif

    {{-- Virtual Account --}}
    @if (! empty($meta['account_number']))
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 space-y-2">
            <p class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase">🏦 Virtual Account</p>
            <div class="flex items-baseline gap-2">
                <span class="text-sm font-medium text-gray-600">{{ strtoupper($meta['bank'] ?? $channel ?? 'Bank') }}</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white tracking-widest">{{ $meta['account_number'] }}</span>
            </div>
        </div>
    @elseif ($method === 'transfer' && ! empty($meta['note']))
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
            <p class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase">🏦 Transfer Bank</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $meta['note'] }}</p>
        </div>
    @endif

    {{-- E-Wallet phone --}}
    @if (! empty($meta['phone']))
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 space-y-1">
            <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase">💳 {{ strtoupper($channel ?? 'E-Wallet') }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-widest">{{ $meta['phone'] }}</p>
        </div>
    @elseif ($method === 'ewallet' && ! empty($meta['note']))
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
            <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase">💳 E-Wallet</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $meta['note'] }}</p>
        </div>
    @endif

    {{-- Deeplink (gateway) --}}
    @if (! empty($meta['deeplink']))
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Link Pembayaran</p>
            <a href="{{ $meta['deeplink'] }}" target="_blank"
               class="inline-flex items-center gap-1 text-primary-600 text-sm hover:underline">
                Buka Halaman Pembayaran ↗
            </a>
        </div>
    @endif

    {{-- Nominal + Expired --}}
    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Nominal</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">
                Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}
            </p>
        </div>
        @if (! empty($meta['expires_at']))
            <div class="text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Berlaku Sampai</p>
                <p class="text-sm text-orange-600 font-medium">
                    {{ \Illuminate\Support\Carbon::parse($meta['expires_at'])->format('d M Y H:i') }}
                </p>
            </div>
        @endif
    </div>

</div>
