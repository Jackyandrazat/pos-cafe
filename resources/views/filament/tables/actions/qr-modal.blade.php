<div class="flex flex-col items-center justify-center p-4 text-center space-y-4">
    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl px-4 py-2 text-xs font-medium text-amber-800 dark:text-amber-300">
        <span class="font-bold text-sm block">Meja {{ $card['table']->table_number }}</span>
        <span>{{ $card['area_name'] }} • Kapasitas {{ $card['table']->capacity ?? 2 }} Pax</span>
    </div>

    <div class="p-4 bg-white rounded-2xl border shadow-sm inline-block">
        <div class="w-56 h-56 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
            {!! $card['qr_svg'] !!}
        </div>
    </div>

    <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400 max-w-sm">
        <p class="font-semibold text-slate-700 dark:text-slate-200">
            Pelanggan cukup scan QR ini dengan kamera smartphone untuk langsung membuka menu dan memesan.
        </p>
    </div>

    <div class="w-full bg-slate-100 dark:bg-slate-900 rounded-lg p-2.5 flex items-center justify-between gap-2 text-left">
        <span class="font-mono text-xs text-slate-600 dark:text-slate-400 truncate select-all">
            {{ $card['target_url'] }}
        </span>
        <button
            type="button"
            onclick="navigator.clipboard.writeText('{{ $card['target_url'] }}'); alert('Link disalin ke clipboard!');"
            class="text-xs px-2.5 py-1 bg-white dark:bg-slate-800 border rounded shadow-xs font-medium hover:bg-slate-50 shrink-0 text-slate-700 dark:text-slate-200"
        >
            Salin Link
        </button>
    </div>
</div>
