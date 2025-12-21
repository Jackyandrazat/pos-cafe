@php
    $meta = $payment->meta ?? [];
@endphp

<div class="space-y-3">
    <div>
        <p class="text-sm text-gray-500">Provider</p>
        <p class="font-semibold text-gray-800">{{ $payment->provider ?? '-' }}</p>
    </div>

    @if (! empty($meta['qr_string']))
        <div class="space-y-1">
            <p class="text-sm text-gray-500">QR String</p>
            <pre class="p-2 bg-gray-100 rounded text-xs overflow-x-auto">{{ $meta['qr_string'] }}</pre>
        </div>
    @endif

    @if (! empty($meta['deeplink']))
        <div>
            <p class="text-sm text-gray-500">Deeplink</p>
            <a href="{{ $meta['deeplink'] }}" target="_blank" class="text-primary-600 text-sm">{{ $meta['deeplink'] }}</a>
        </div>
    @endif

    @if (! empty($meta['expires_at']))
        <div>
            <p class="text-sm text-gray-500">Berlaku sampai</p>
            <p class="text-sm">{{ \Illuminate\Support\Carbon::parse($meta['expires_at'])->format('d M Y H:i') }}</p>
        </div>
    @endif

    <div>
        <p class="text-sm text-gray-500">Nominal</p>
        <p class="text-lg font-semibold">Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</p>
    </div>
</div>
