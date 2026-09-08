<x-filament::page>
    <div 
        x-data="kitchenAudio()" 
        x-init="init()"
        class="kds-container space-y-5"
    >
        {{-- Top Bar: Filters, Audio Controls, Fullscreen, Clock & Refresh --}}
        <div class="kds-topbar">
            {{-- Status Filter Tabs with Counters --}}
            <div class="flex items-center gap-2 overflow-x-auto py-0.5 max-w-full">
                <button 
                    type="button"
                    wire:click="setFilter('active')"
                    class="kds-tab-btn {{ $statusFilter === 'active' ? 'active-active' : 'inactive' }}"
                >
                    <span>🔥 Pesanan Aktif</span>
                    <span class="kds-badge-count">{{ $activeCount }}</span>
                </button>

                <button 
                    type="button"
                    wire:click="setFilter('ready')"
                    class="kds-tab-btn {{ $statusFilter === 'ready' ? 'active-ready' : 'inactive' }}"
                >
                    <span>☕ Siap Saji</span>
                    <span class="kds-badge-count">{{ $readyCount }}</span>
                </button>

                <button 
                    type="button"
                    wire:click="setFilter('completed')"
                    class="kds-tab-btn {{ $statusFilter === 'completed' ? 'active-completed' : 'inactive' }}"
                >
                    <span>✅ Selesai Hari Ini</span>
                    <span class="kds-badge-count">{{ $completedCount }}</span>
                </button>

                @if($unpaidCount > 0)
                <button 
                    type="button"
                    wire:click="setFilter('unpaid')"
                    class="kds-tab-btn {{ $statusFilter === 'unpaid' ? 'active-active' : 'inactive' }}"
                    style="{{ $statusFilter === 'unpaid' ? 'background: #d97706; color: #ffffff;' : '' }}"
                    title="Pesanan yang belum dibayar / menunggu uang fisik di kasir"
                >
                    <span>⏳ Menunggu Kasir</span>
                    <span class="kds-badge-count" style="background: rgba(0,0,0,0.25); color: #ffffff;">{{ $unpaidCount }}</span>
                </button>
                @endif
            </div>

            {{-- Kitchen Toolbar: Sound, Live Clock, Fullscreen, Refresh --}}
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Live Kitchen Digital Clock --}}
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 font-mono text-xs font-bold border border-slate-200 dark:border-slate-700/60 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="currentTime"></span>
                </div>

                {{-- Sound Toggle with glowing LED --}}
                <button
                    type="button"
                    @click="toggleSound()"
                    :class="soundEnabled ? 'kds-sound-active' : ''"
                    class="kds-tool-btn"
                    title="Aktifkan/Bisukan suara lonceng pesanan baru"
                >
                    <span :class="soundEnabled ? 'kds-led' : 'kds-led-muted'"></span>
                    <span x-text="soundEnabled ? 'Suara: Aktif' : 'Suara: Bisu'"></span>
                </button>

                {{-- Test Sound Chime --}}
                <button
                    type="button"
                    @click="playBellChime()"
                    class="kds-tool-btn"
                    title="Uji coba bunyikan lonceng pesanan dapur"
                >
                    <span>🔊</span>
                    <span class="hidden md:inline">Tes Suara</span>
                </button>

                {{-- Fullscreen Toggle --}}
                <button
                    type="button"
                    @click="toggleFullscreen()"
                    class="kds-tool-btn"
                    title="Mode Layar Penuh Kitchen Display"
                >
                    <svg x-show="!isFullscreen" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <svg x-show="isFullscreen" class="w-3.5 h-3.5" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m7 7l5 5m0 0h-4m4 0v-4M9 15l-5 5m0 0v-4m0 4h4m11-11l5-5m-5 0v4m0-4h4" />
                    </svg>
                    <span x-text="isFullscreen ? 'Kecilkan' : 'Layar Penuh'"></span>
                </button>

                {{-- Live Sync Pulse Indicator --}}
                <div class="hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm" title="Sinkronisasi otomatis aktif setiap 3 detik">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live (3s)</span>
                </div>

                {{-- Refresh Button --}}
                <button
                    type="button"
                    wire:click="refreshOrders"
                    class="kds-tool-btn transition-transform duration-500"
                    title="Muat ulang pesanan secara manual"
                >
                    <svg wire:loading.class="animate-spin" wire:target="refreshOrders" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden md:inline">Refresh</span>
                </button>
            </div>
        </div>

        {{-- Orders Grid (Fast 3-second live polling) --}}
        <div wire:poll.3s="refreshOrders" class="kds-grid mt-3">
            @forelse ($orders as $order)
                @php
                    $status = $order['status'];
                    $isCompleted = ($order['is_completed'] ?? ($status === 'completed'));
                    $elapsed = $isCompleted ? 0 : ($order['elapsed_minutes'] ?? 0);
                    
                    // Card Urgency & Stripe styling
                    if ($isCompleted) {
                        $cardUrgency = '';
                        $stripeClass = 'kds-stripe-completed';
                        $timeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold';
                        $timeIcon = '✓';
                    } elseif ($elapsed >= 20) {
                        $cardUrgency = 'urgency-high';
                        $stripeClass = 'kds-stripe-urgent';
                        $timeClass = 'kds-time-urgent';
                        $timeIcon = '⚠️';
                    } elseif ($elapsed >= 10) {
                        $cardUrgency = 'urgency-medium';
                        $stripeClass = 'kds-stripe-preparing';
                        $timeClass = 'kds-time-medium';
                        $timeIcon = '⏱️';
                    } else {
                        $cardUrgency = '';
                        $stripeClass = match ($status) {
                            'ready' => 'kds-stripe-ready',
                            'preparing' => 'kds-stripe-preparing',
                            default => 'kds-stripe-pending',
                        };
                        $timeClass = 'kds-time-normal';
                        $timeIcon = '⏱️';
                    }

                    $orderType = strtolower($order['order_type'] ?? 'dine_in');
                    $orderTypeLabel = match($orderType) {
                        'take_away', 'takeaway' => 'Take Away',
                        'delivery' => 'Delivery',
                        default => 'Dine In',
                    };
                @endphp

                <div class="kds-card {{ $cardUrgency }}" wire:key="order-card-{{ $order['id'] }}">
                    {{-- Status Accent Stripe --}}
                    <div class="kds-stripe {{ $stripeClass }}"></div>

                    <div>
                        {{-- Ticket Header: Order #, Table/Type, Customer, Elapsed Time --}}
                        <div class="kds-ticket-header">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="kds-order-num">#{{ $order['id'] }}</span>

                                        @if (!empty($order['table']))
                                            <span class="kds-table-pill">
                                                <svg class="w-3.5 h-3.5 inline-block -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Meja {{ $order['table'] }}
                                            </span>
                                        @else
                                            <span class="kds-takeaway-pill">
                                                @if ($orderType === 'delivery')
                                                    🛵 {{ $orderTypeLabel }}
                                                @else
                                                    🛍️ {{ $orderTypeLabel }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>

                                    @if (!empty($order['customer']))
                                        <div class="flex items-center gap-1 text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate max-w-[170px]">{{ $order['customer'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Elapsed Time Badge & Timestamp --}}
                                <div class="text-right flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="kds-time-badge {{ $timeClass }}" title="{{ $isCompleted ? 'Total durasi pembuatan pesanan' : 'Waktu tunggu sejak dipesan' }}">
                                        <span>{{ $timeIcon }}</span>
                                        <span>{{ $order['elapsed_text'] }}</span>
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <a 
                                            href="{{ route('orders.print.kitchen', ['order' => $order['id']]) }}" 
                                            target="_blank" 
                                            class="p-0.5 text-slate-400 hover:text-amber-600 transition-colors"
                                            title="Cetak Tiket Dapur (Kitchen Docket)"
                                        >
                                            🖨️
                                        </a>
                                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 font-semibold">
                                            @if ($isCompleted && !empty($order['completed_at']))
                                                {{ $order['created_at'] }} → {{ $order['completed_at'] }}
                                            @else
                                                {{ $order['created_at'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Order Notes Box (if any) --}}
                        @if (!empty($order['notes']))
                            <div class="kds-notes-box">
                                <svg class="w-4 h-4 flex-shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <div class="font-medium leading-snug">
                                    <strong class="font-bold">Catatan:</strong> {{ $order['notes'] }}
                                </div>
                            </div>
                        @endif

                        {{-- Order Items List with Tap-to-Strikethrough --}}
                        <div class="kds-items-list">
                            @foreach ($order['items'] as $item)
                                <div 
                                    x-data="{ done: false }" 
                                    @click="done = !done"
                                    :class="{ 'is-done': done }"
                                    class="kds-item-card"
                                    title="Klik untuk menandai menu sudah dimasak/disajikan"
                                >
                                    <div class="flex items-start justify-between gap-2.5">
                                        <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                            <span class="kds-qty-badge" :class="{ 'line-through opacity-70': done }">
                                                {{ $item['qty'] }}x
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug break-words">
                                                    {{ $item['name'] }}
                                                </div>

                                                {{-- Size Tag --}}
                                                @if (!empty($item['size']))
                                                    <span class="kds-size-tag mt-1">
                                                        {{ $item['size'] }}
                                                    </span>
                                                @endif

                                                {{-- Toppings --}}
                                                @if (!empty($item['toppings']) && count($item['toppings']) > 0)
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach ($item['toppings'] as $topping)
                                                            <span class="kds-topping-tag">
                                                                + {{ $topping }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Item specific note --}}
                                                @if (!empty($item['notes']))
                                                    <p class="text-xs text-amber-700 dark:text-amber-300 font-medium italic mt-1 bg-amber-50/60 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-200/50 dark:border-amber-800/40">
                                                        📌 {{ $item['notes'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Checkmark icon status --}}
                                        <div class="flex-shrink-0 pt-0.5">
                                            <div 
                                                class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                                :class="done ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600 text-transparent'"
                                            >
                                                <svg class="w-3.5 h-3.5 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Footer Action Buttons --}}
                    <div class="kds-card-actions">
                        @if ($status !== 'preparing' && $status !== 'ready' && $status !== 'completed')
                            <button
                                type="button"
                                wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Preparing->value }}')"
                                class="kds-btn-cook"
                                title="Mulai memasak pesanan ini"
                            >
                                <span>👨‍🍳</span>
                                <span>Mulai Masak</span>
                            </button>

                            <button
                                type="button"
                                wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Ready->value }}')"
                                class="kds-btn-ready"
                                title="Langsung tandai siap saji"
                            >
                                <span>✨</span>
                                <span>Siap Saji</span>
                            </button>
                        @elseif ($status === 'preparing')
                            <button
                                type="button"
                                wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Ready->value }}')"
                                class="kds-btn-ready"
                                title="Tandai pesanan telah matang dan siap disajikan"
                            >
                                <span>✨</span>
                                <span>Siap Saji</span>
                            </button>

                            <button
                                type="button"
                                wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Completed->value }}')"
                                class="kds-btn-done"
                                title="Selesaikan pesanan langsung"
                            >
                                <span>✓ Selesai</span>
                            </button>
                        @elseif ($status === 'ready')
                            <button
                                type="button"
                                wire:click="advanceStatus({{ $order['id'] }}, '{{ \App\Enums\OrderStatus::Completed->value }}')"
                                class="kds-btn-ready"
                                title="Pesanan telah diantar atau diambil oleh pelanggan"
                            >
                                <span>✓</span>
                                <span>Diambil / Selesai</span>
                            </button>
                        @else
                            <div class="w-full py-1 text-center text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                ✅ Pesanan Selesai
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3 text-3xl shadow-inner">
                        🍳
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Pesanan Antrean</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        Pesanan baru yang masuk dari self-order tablet atau POS kasir akan otomatis muncul seketika disertai lonceng notifikasi dapur.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Alpine Web Audio Chime & Kitchen Tools Script --}}
    <script>
        function kitchenAudio() {
            return {
                soundEnabled: true,
                audioCtx: null,
                isFullscreen: false,
                currentTime: '',

                init() {
                    const saved = localStorage.getItem('kds_sound_enabled');
                    if (saved !== null) {
                        this.soundEnabled = (saved === 'true');
                    }

                    this.updateClock();
                    setInterval(() => {
                        this.updateClock();
                    }, 1000);

                    // Dengarkan event play-order-chime dari Livewire saat ada order baru masuk
                    window.addEventListener('play-order-chime', (event) => {
                        if (this.soundEnabled) {
                            this.playBellChime();
                        }
                    });

                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                },

                updateClock() {
                    const d = new Date();
                    this.currentTime = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch((err) => {
                            console.warn('Fullscreen request failed:', err);
                        });
                    } else {
                        document.exitFullscreen().catch((err) => {
                            console.warn('Exit fullscreen failed:', err);
                        });
                    }
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    localStorage.setItem('kds_sound_enabled', this.soundEnabled);
                    if (this.soundEnabled) {
                        this.playBellChime();
                    }
                },

                getAudioContext() {
                    if (!this.audioCtx) {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        this.audioCtx = new AudioContext();
                    }
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                    return this.audioCtx;
                },

                playBellChime() {
                    try {
                        const ctx = this.getAudioContext();
                        const now = ctx.currentTime;

                        // Dual-tone digital service bell (A5 = 880Hz -> E5 = 659.25Hz with exponential decay)
                        const playTone = (freq, startTime, duration, gainVal) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();

                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(freq, startTime);

                            // Envelope: fast attack, exponential decay (bell chime)
                            gain.gain.setValueAtTime(0.0001, startTime);
                            gain.gain.exponentialRampToValueAtTime(gainVal, startTime + 0.015);
                            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                            osc.connect(gain);
                            gain.connect(ctx.destination);

                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };

                        // 1st note (High crisp bell)
                        playTone(880, now, 0.55, 0.35);
                        playTone(1760, now, 0.35, 0.08);

                        // 2nd note (Warm sustaining bell)
                        playTone(659.25, now + 0.16, 0.75, 0.4);
                        playTone(1318.5, now + 0.16, 0.45, 0.09);
                    } catch (err) {
                        console.error('Audio chime error:', err);
                    }
                }
            };
        }
    </script>
</x-filament::page>
