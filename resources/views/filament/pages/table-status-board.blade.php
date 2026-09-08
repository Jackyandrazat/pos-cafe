<x-filament::page>
    <div class="tsb-wrapper space-y-5" wire:poll.15s="refreshData" x-data="posAudio()">

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- TOP HEADER: LIVE FLOOR MONITOR & QUICK ACTIONS               --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        <div class="tsb-topbar">
            <div class="flex items-center gap-3.5">
                <div class="tsb-topbar-icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="tsb-title">
                            Monitor Meja & Denah POS
                        </h2>
                        <span class="tsb-live-badge">
                            <span class="tsb-pulse-dot"></span>
                            <span>Live Sync</span>
                        </span>
                    </div>
                    <p class="tsb-subtitle">
                        Sinkronisasi otomatis setiap 15 detik • Dilengkapi notifikasi audio pesanan & panggilan pelayan
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Sound Notification Toggle --}}
                <button type="button"
                    @click="toggleSound()"
                    class="tsb-header-btn tsb-btn-sound"
                    :class="{ 'muted': !soundEnabled }"
                    title="Aktifkan / Senyapkan suara notifikasi pesanan dan pelayan">
                    <span x-text="soundEnabled ? '🔔' : '🔕'"></span>
                    <span x-text="soundEnabled ? 'Suara: Nyala' : 'Suara: Mute'"></span>
                </button>

                @if ($stats['cleaning'] > 0)
                    <button type="button"
                        wire:click="finishAllCleaning"
                        class="tsb-header-btn tsb-btn-clean-all">
                        <span>🧹</span>
                        <span>Sapu Bersih Semua ({{ $stats['cleaning'] }} Meja)</span>
                    </button>
                @endif

                <button type="button"
                    wire:click="refreshData"
                    wire:loading.attr="disabled"
                    class="tsb-header-btn tsb-btn-refresh">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh</span>
                </button>

                <a href="{{ \App\Filament\Resources\TableQueueEntryResource::getUrl() }}"
                    class="tsb-header-btn tsb-btn-queue-link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Kelola Antrean</span>
                </a>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- URGENT WAITER CALL BANNER                                    --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        @php
            $callingTables = collect($tables)->where('calling_waiter', true);
        @endphp
        @if($callingTables->isNotEmpty())
            <div class="tsb-waiter-banner">
                <div class="tsb-waiter-banner-left">
                    <div class="tsb-waiter-icon">
                        <span>🛎️</span>
                    </div>
                    <div>
                        <div class="tsb-waiter-title-row">
                            <h3 class="tsb-waiter-title">
                                {{ $callingTables->count() }} Meja Panggil Pelayan!
                            </h3>
                            <span class="tsb-waiter-pulse-badge">
                                <span class="tsb-pulse-dot" style="background: #e11d48; box-shadow: 0 0 8px #e11d48;"></span>
                                <span>Butuh Bantuan</span>
                            </span>
                        </div>
                        <p class="tsb-waiter-subtitle">
                            Tamu di meja membutuhkan bantuan atau tagihan bill. Silakan staf hampiri meja terkait.
                        </p>
                    </div>
                </div>

                <div class="tsb-waiter-chips-wrap">
                    @foreach($callingTables as $cTable)
                        <div class="tsb-waiter-chip">
                            <div class="tsb-waiter-chip-info">
                                <div class="tsb-waiter-chip-head">
                                    <span class="tsb-waiter-puck">Meja {{ $cTable['table_number'] }}</span>
                                    <span class="tsb-waiter-reason-tag">{{ $cTable['waiter_call_reason_label'] }}</span>
                                </div>
                                <div class="tsb-waiter-chip-sub">
                                    <span>⏱️ {{ $cTable['waiter_waited_text'] }}</span>
                                    @if(!empty($cTable['waiter_call_notes']))
                                        <span class="tsb-waiter-note-snippet" title="{{ $cTable['waiter_call_notes'] }}">
                                            • "{{ \Illuminate\Support\Str::limit($cTable['waiter_call_notes'], 25) }}"
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <button type="button"
                                wire:click="dismissWaiterCall({{ $cTable['id'] }})"
                                class="tsb-btn-waiter-dismiss"
                                title="Tandai sudah dilayani & selesaikan panggilan">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Selesai</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- TACTILE 3D KPI METRIC CARDS (INTERACTIVE 1-CLICK STATUS TABS) --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        <div class="tsb-kpi-grid">
            {{-- 1. All Tables --}}
            <button type="button"
                wire:click="setStatusFilter('all')"
                class="tsb-kpi-card tsb-kpi-all {{ $statusFilter === 'all' ? 'active' : '' }}">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span>📋</span>
                        <span>Semua Meja</span>
                    </span>
                    <span class="tsb-kpi-tag">
                        {{ $stats['seats_total'] }} Kursi
                    </span>
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['total'] }}</span>
                    <span class="tsb-kpi-unit">meja</span>
                </div>
                <div class="tsb-kpi-footer">
                    <span>Okupansi</span>
                    <span style="font-weight: 800;">{{ $stats['occupancy'] }}%</span>
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>

            {{-- 2. Available (Kosong) --}}
            <button type="button"
                wire:click="setStatusFilter('available')"
                class="tsb-kpi-card tsb-kpi-available {{ $statusFilter === 'available' ? 'active' : '' }}">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <span>Kosong</span>
                    </span>
                    <span class="tsb-kpi-tag">
                        Siap Pakai
                    </span>
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['available'] }}</span>
                    <span class="tsb-kpi-unit">meja</span>
                </div>
                <div class="tsb-kpi-footer">
                    <span>Bebas dipesan</span>
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>

            {{-- 3. Occupied (Terisi) --}}
            <button type="button"
                wire:click="setStatusFilter('occupied')"
                class="tsb-kpi-card tsb-kpi-occupied {{ $statusFilter === 'occupied' ? 'active' : '' }}">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #f43f5e; display: inline-block;"></span>
                        <span>Terisi</span>
                    </span>
                    <span class="tsb-kpi-tag">
                        Dining
                    </span>
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['occupied'] }}</span>
                    <span class="tsb-kpi-unit">meja</span>
                </div>
                <div class="tsb-kpi-footer">
                    <span>{{ $stats['seats_used'] }} kursi terisi</span>
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>

            {{-- 4. Reserved --}}
            <button type="button"
                wire:click="setStatusFilter('reserved')"
                class="tsb-kpi-card tsb-kpi-reserved {{ $statusFilter === 'reserved' ? 'active' : '' }}">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #8b5cf6; display: inline-block;"></span>
                        <span>Reserved</span>
                    </span>
                    <span class="tsb-kpi-tag">
                        Booked
                    </span>
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['reserved'] }}</span>
                    <span class="tsb-kpi-unit">meja</span>
                </div>
                <div class="tsb-kpi-footer">
                    <span>Tamu terjadwal</span>
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>

            {{-- 5. Cleaning --}}
            <button type="button"
                wire:click="setStatusFilter('cleaning')"
                class="tsb-kpi-card tsb-kpi-cleaning {{ $statusFilter === 'cleaning' ? 'active' : '' }}">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                        <span>Cleaning</span>
                    </span>
                    @if ($stats['cleaning'] > 0)
                        <span class="tsb-kpi-tag" style="background: #f59e0b; color: #ffffff; font-weight: 900;">
                            {{ $stats['cleaning'] }} Perlu!
                        </span>
                    @else
                        <span class="tsb-kpi-tag">
                            Bersih
                        </span>
                    @endif
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['cleaning'] }}</span>
                    <span class="tsb-kpi-unit">meja</span>
                </div>
                <div class="tsb-kpi-footer">
                    <span>Menunggu disterilkan</span>
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>

            {{-- 6. Host Stand / Queue --}}
            <button type="button"
                wire:click="setMobileTab('queue')"
                class="tsb-kpi-card tsb-kpi-queue">
                <div class="tsb-kpi-header">
                    <span class="tsb-kpi-label">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                        <span>Antrean Tamu</span>
                    </span>
                    <span class="tsb-kpi-tag">
                        {{ $stats['queue_guests'] }} Pax
                    </span>
                </div>
                <div class="tsb-kpi-count-wrap">
                    <span class="tsb-kpi-num">{{ $stats['queue_waiting'] }}</span>
                    <span class="tsb-kpi-unit">grup</span>
                </div>
                <div class="tsb-kpi-footer">
                    @if ($stats['queue_longest'] > 0)
                        <span>Maks: <strong>{{ $stats['queue_longest'] }}m</strong></span>
                    @else
                        <span>Tidak ada antrean</span>
                    @endif
                </div>
                <div class="tsb-kpi-stripe"></div>
            </button>
        </div>

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- TACTILE FLOOR / AREA SELECTOR BAR                             --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        <div class="tsb-area-bar">
            <div class="flex flex-wrap items-center gap-2">
                <span class="tsb-area-label">
                    <span>🏢</span>
                    <span>Area Denah:</span>
                </span>

                <button type="button"
                    wire:click="selectArea(null)"
                    class="tsb-area-pill {{ $selectedArea === null ? 'active' : '' }}">
                    Semua Area ({{ $this->totalAllTablesCount }})
                </button>

                @foreach ($this->areas as $area)
                    <button type="button"
                        wire:click="selectArea({{ $area->id }})"
                        class="tsb-area-pill {{ $selectedArea === $area->id ? 'active' : '' }}">
                        {{ $area->name }} ({{ $this->areaCounts[$area->id] ?? 0 }})
                    </button>
                @endforeach
            </div>

            {{-- Mobile Switcher --}}
            <div class="flex lg:hidden bg-slate-200 dark:bg-slate-800 p-1 rounded-xl border border-slate-300 dark:border-slate-700">
                <button type="button"
                    wire:click="setMobileTab('tables')"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $mobileTab === 'tables' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'text-slate-600 dark:text-slate-400' }}">
                    🪑 Meja ({{ count($tables) }})
                </button>
                <button type="button"
                    wire:click="setMobileTab('queue')"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $mobileTab === 'queue' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'text-slate-600 dark:text-slate-400' }}">
                    ⏳ Antrean ({{ count($queueEntries) }})
                </button>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MAIN CONTENT: 2-COLUMN LAYOUT (TABLE GRID & QUEUE SIDEBAR)    --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- COLUMN 1: FLOOR TABLES GRID (lg:col-span-8 / xl:col-span-9)  --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4 lg:col-span-8 xl:col-span-9 {{ $mobileTab === 'queue' ? 'hidden lg:block' : 'block' }}">
                <div class="tsb-grid">
                    @forelse ($tables as $table)
                        @php
                            $status = $table['status'];
                            $hasOrder = !empty($table['order']);
                            $order = $table['order'];
                            $elapsedMinutes = $order['elapsed_minutes'] ?? 0;
                        @endphp

                        <div wire:key="table-card-{{ $table['id'] }}"
                            class="tsb-card tsb-card-{{ $status }}">

                            {{-- Status Color Top Stripe --}}
                            <div class="tsb-card-stripe tsb-card-stripe-{{ $status }}"></div>

                            {{-- Card Body --}}
                            <div class="tsb-card-body">
                                {{-- Header: Table Puck, Meta & Status Badge --}}
                                <div>
                                    @if (!empty($table['calling_waiter']))
                                        <div class="tsb-card-waiter-alert">
                                            <div class="tsb-card-waiter-header">
                                                <div class="tsb-card-waiter-badge">
                                                    <span>🛎️</span>
                                                    <span>Panggil Pelayan!</span>
                                                </div>
                                                <span class="tsb-card-waiter-time">
                                                    ⏱️ {{ $table['waiter_waited_text'] }}
                                                </span>
                                            </div>
                                            <div class="tsb-card-waiter-reason">
                                                {{ $table['waiter_call_reason_label'] }}
                                            </div>
                                            @if(!empty($table['waiter_call_notes']))
                                                <p class="tsb-card-waiter-note">
                                                    "{{ $table['waiter_call_notes'] }}"
                                                </p>
                                            @endif
                                            <div class="tsb-card-waiter-actions">
                                                <button type="button"
                                                    wire:click="dismissWaiterCall({{ $table['id'] }})"
                                                    class="tsb-btn-waiter-dismiss-card">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Sudah Dilayani (Selesai)</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-start justify-between gap-2.5">
                                        {{-- 3D Table Puck --}}
                                        <div class="flex items-center gap-3">
                                            <div class="tsb-puck tsb-puck-{{ $status }}">
                                                <span class="tsb-puck-tag">Meja</span>
                                                <span class="tsb-puck-num">{{ $table['table_number'] }}</span>
                                            </div>

                                            <div>
                                                <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; display: block;">
                                                    {{ $table['area_name'] ?? 'Area Utama' }}
                                                </span>
                                                <div style="font-size: 0.8rem; font-weight: 800; color: #334155; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.35rem;">
                                                    <span>👥</span>
                                                    <span>{{ $table['capacity'] }} Pax</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Status Badge Pill --}}
                                        <span class="tsb-badge tsb-badge-{{ $status }}">
                                            <span class="tsb-badge-dot"></span>
                                            <span>{{ $table['status_label'] }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Contextual Middle Content Box --}}
                                <div>
                                    @if ($status === 'occupied')
                                        @if ($hasOrder)
                                            <div class="tsb-box tsb-box-occupied space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span style="font-weight: 800; display: flex; align-items: center; gap: 0.25rem;">
                                                        <span>🧾</span>
                                                        <a href="{{ $order['edit_url'] }}" title="Lihat / Edit Pesanan #{{ $order['id'] }}" style="color: inherit; text-decoration: none;" class="hover:underline">
                                                            Order #{{ $order['id'] }}
                                                        </a>
                                                    </span>
                                                    <span style="font-weight: 800; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 0.5rem; background: {{ $elapsedMinutes >= 45 ? '#f59e0b' : '#e11d48' }}; color: #ffffff;">
                                                        ⏱️ {{ $order['elapsed_text'] }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between pt-0.5">
                                                    <span style="color: #64748b; font-weight: 600;">
                                                        {{ $order['items_count'] }} menu • {{ $order['status_label'] }}
                                                    </span>
                                                    <span style="font-weight: 900; font-size: 0.9rem; color: #0f172a;">
                                                        Rp {{ number_format($order['total'], 0, ',', '.') }}
                                                    </span>
                                                </div>

                                                @if (!empty($table['guest_name']))
                                                    <div style="font-size: 0.7rem; padding-top: 0.35rem; border-top: 1px dashed rgba(225, 29, 72, 0.3); color: #be123c; font-weight: 700; display: flex; align-items: center; gap: 0.25rem;">
                                                        <span>👤</span>
                                                        <span class="truncate">{{ $table['guest_name'] }} ({{ $table['party_size'] }} pax)</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="tsb-box tsb-box-occupied space-y-1">
                                                <p style="font-weight: 800; display: flex; align-items: center; gap: 0.3rem;">
                                                    <span>👥</span>
                                                    <span>Tamu Sudah Duduk</span>
                                                </p>
                                                <p style="font-size: 0.7rem; color: #64748b;">Belum ada pesanan aktif di kasir.</p>
                                                @if (!empty($table['guest_name']))
                                                    <p style="font-size: 0.7rem; font-weight: 700; color: #be123c;">
                                                        👤 {{ $table['guest_name'] }} ({{ $table['party_size'] }} pax)
                                                    </p>
                                                @endif
                                            </div>
                                        @endif

                                    @elseif ($status === 'cleaning')
                                        <div class="tsb-box tsb-box-cleaning space-y-1">
                                            <p style="font-weight: 800; display: flex; align-items: center; gap: 0.35rem; color: #b45309;">
                                                <span>🧹</span>
                                                <span>Perlu Dibersihkan Segera</span>
                                            </p>
                                            <p style="font-size: 0.7rem; line-height: 1.35; color: #92400e;">
                                                Tamu sudah selesai. Bersihkan dan semprot meja sebelum tamu baru masuk.
                                            </p>
                                        </div>

                                    @elseif ($status === 'reserved')
                                        <div class="tsb-box tsb-box-reserved space-y-1">
                                            <p style="font-weight: 800; display: flex; align-items: center; gap: 0.35rem; color: #6d28d9;">
                                                <span>📅</span>
                                                <span>Reservasi Terjadwal</span>
                                            </p>
                                            <p style="font-size: 0.7rem; line-height: 1.35; color: #581c87;">
                                                {{ $table['notes'] ?: 'Disimpan untuk reservasi pelanggan' }}
                                            </p>
                                        </div>

                                    @else
                                        <div class="tsb-box tsb-box-available space-y-1">
                                            <p style="font-weight: 800; display: flex; align-items: center; gap: 0.35rem; color: #15803d;">
                                                <span>✨</span>
                                                <span>Meja Bersih & Siap Pakai</span>
                                            </p>
                                            <p style="font-size: 0.7rem; color: #166534;">
                                                Siap untuk menerima tamu baru atau pesanan kasir.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer 1-Tap POS Buttons --}}
                            <div class="tsb-card-footer">
                                @if ($status === 'available')
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ $table['create_order_url'] }}"
                                            class="tsb-btn-primary flex-1">
                                            + Pesanan Baru
                                        </a>

                                        <button type="button"
                                            wire:click="setStatus({{ $table['id'] }}, 'reserved')"
                                            title="Tandai Reserved"
                                            class="tsb-btn-secondary"
                                            style="color: #7c3aed;">
                                            Book
                                        </button>

                                        <button type="button"
                                            wire:click="setStatus({{ $table['id'] }}, 'occupied')"
                                            title="Tandai Terisi (Dudukkan Tamu)"
                                            class="tsb-btn-secondary"
                                            style="color: #e11d48;">
                                            Isi
                                        </button>
                                    </div>

                                @elseif ($status === 'occupied')
                                    <div class="flex items-center gap-1.5">
                                        @if ($hasOrder)
                                            <a href="{{ $order['payment_url'] }}"
                                                class="tsb-btn-danger flex-1 truncate text-center font-extrabold"
                                                title="Buka Kasir Pembayaran Order #{{ $order['id'] }}">
                                                Kasir #{{ $order['id'] }}
                                            </a>
                                            <a href="{{ $order['edit_url'] }}"
                                                class="tsb-btn-secondary"
                                                title="Lihat / Edit Pesanan #{{ $order['id'] }}"
                                                style="padding: 0.55rem 0.65rem; border-radius: 0.75rem;">
                                                ✏️
                                            </a>
                                        @else
                                            <a href="{{ $table['create_order_url'] }}"
                                                class="tsb-btn-danger flex-1 truncate text-center">
                                                + Buat Pesanan
                                            </a>
                                        @endif

                                        <button type="button"
                                            wire:click="clearTable({{ $table['id'] }})"
                                            title="Tamu Pulang ➔ Masuk Cleaning"
                                            class="tsb-btn-secondary"
                                            style="color: #b45309; background: #fef3c7; border-color: #fde68a;">
                                            🧹 Pulang
                                        </button>
                                    </div>

                                @elseif ($status === 'cleaning')
                                    <button type="button"
                                        wire:click="setStatus({{ $table['id'] }}, 'available')"
                                        class="tsb-btn-success w-full flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Sudah Bersih (Siap Pakai)</span>
                                    </button>

                                @elseif ($status === 'reserved')
                                    <div class="flex items-center gap-1.5">
                                        <button type="button"
                                            wire:click="setStatus({{ $table['id'] }}, 'occupied')"
                                            class="tsb-btn-purple flex-1">
                                            Tamu Hadir (Dudukkan)
                                        </button>

                                        <button type="button"
                                            wire:click="setStatus({{ $table['id'] }}, 'available')"
                                            title="Batal Reservasi"
                                            class="tsb-btn-secondary">
                                            Batal
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                    @empty
                        <div class="col-span-full tsb-empty-card">
                            <div class="tsb-empty-icon">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">
                                Tidak Ada Meja Yang Cocok
                            </h4>
                            <p style="font-size: 0.8rem; color: #64748b; max-width: 24rem; margin: 0.5rem auto 1.25rem;">
                                @if ($statusFilter !== 'all')
                                    Tidak ada meja dengan status <strong>{{ $statusOptions[$statusFilter] ?? $statusFilter }}</strong> pada area yang sedang dipilih.
                                @else
                                    Belum ada data meja pada area ini.
                                @endif
                            </p>
                            @if ($statusFilter !== 'all')
                                <button type="button"
                                    wire:click="setStatusFilter('all')"
                                    class="tsb-btn-primary"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem;">
                                    <span>🔄</span>
                                    <span>Tampilkan Semua Meja (Reset Filter)</span>
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- COLUMN 2: HOST STAND / ANTREAN DRAWER (lg:col-span-4 / xl:3) --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4 lg:col-span-4 xl:col-span-3 {{ $mobileTab === 'tables' ? 'hidden lg:block' : 'block' }}">
                <div class="tsb-host-card space-y-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3" style="border-bottom: 1.5px solid #e2e8f0;">
                        <div class="flex items-center gap-2.5">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: linear-gradient(135deg, #4f46e5, #4338ca); color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-left: 0.7rem;">
                                    Host Stand
                                </h3>
                                <p style="font-size: 0.7rem; color: #64748b; margin-left: 0.7rem; margin-bottom: 0.5rem">Waiting list antrean</p>
                            </div>
                        </div>
                        <span class="tsb-badge" style="background: #e0e7ff; color: #3730a3; border-color: #c7d2fe;">
                            {{ count($queueEntries) }} Menunggu
                        </span>
                    </div>

                    {{-- Quick Add Queue Form --}}
                    <div class="tsb-form-box space-y-3">
                        <p style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #334155; display: flex; align-items: center; gap: 0.35rem;">
                            <span>✨</span>
                            <span>+ Tambah Tamu Antrean</span>
                        </p>

                        <div class="space-y-2.5">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.25rem;">
                                    Nama Tamu / Pemesan
                                </label>
                                <input type="text"
                                    wire:model.defer="newGuestName"
                                    placeholder="Contoh: Bpk. Budi"
                                    style="width: 100%; border-radius: 0.75rem; border: 1.5px solid #cbd5e1; font-size: 0.75rem; padding: 0.5rem 0.75rem; background: #ffffff; color: #0f172a;" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between" style="margin-bottom: 0.25rem;">
                                    <label style="font-size: 0.75rem; font-weight: 700; color: #475569;">Jumlah Orang (Pax)</label>
                                    <span style="font-size: 0.8rem; font-weight: 800; color: #ea580c;">{{ $newPartySize }} Pax</span>
                                </div>
                                <div class="grid grid-cols-6 gap-1">
                                    @foreach ([1, 2, 3, 4, 5, 6] as $size)
                                        <button type="button"
                                            wire:click="setPartySize({{ $size }})"
                                            class="tsb-pax-btn {{ $newPartySize === $size ? 'active' : '' }}">
                                            {{ $size }}{{ $size === 6 ? '+' : '' }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button"
                                wire:click="addQueueEntry"
                                style="width: 100%; margin-top: 0.5rem; background: linear-gradient(135deg, #4f46e5, #4338ca); color: #ffffff; font-weight: 800; font-size: 0.75rem; padding: 0.65rem; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                                + Masukkan ke Antrean
                            </button>
                        </div>
                    </div>

                    {{-- Active Queue Entries List --}}
                    <div class="space-y-3 pt-1">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 px-1">
                            <span>Daftar Menunggu:</span>
                            <span>{{ $this->availableTables ? count($this->availableTables) . ' Meja Kosong' : 'Meja Penuh' }}</span>
                        </div>

                        @forelse ($queueEntries as $entry)
                            @php
                                $urgencyClass = match ($entry['urgency']) {
                                    'high' => 'tsb-ticket-high',
                                    'medium' => 'tsb-ticket-med',
                                    default => 'tsb-ticket-low',
                                };
                            @endphp

                            <div wire:key="queue-entry-{{ $entry['id'] }}"
                                class="tsb-ticket {{ $urgencyClass }} space-y-3">

                                {{-- Guest Info Header --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 style="font-size: 0.85rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                                            {{ $entry['guest_name'] }}
                                        </h4>
                                        <p style="font-size: 0.7rem; color: #64748b; display: flex; align-items: center; gap: 0.3rem; margin-top: 0.2rem;">
                                            <span>👥 <strong>{{ $entry['party_size'] }} Pax</strong></span>
                                            <span>•</span>
                                            <span>⏱️ {{ $entry['waited_text'] }} lalu ({{ $entry['check_in_time'] }})</span>
                                        </p>
                                    </div>

                                    @if ($entry['status'] === 'called')
                                        <span class="tsb-badge" style="background: #f3e8ff; color: #6d28d9; border-color: #d8b4fe;">
                                            📢 Dipanggil
                                        </span>
                                    @else
                                        <span class="tsb-badge" style="{{ $entry['urgency'] === 'high' ? 'background: #fee2e2; color: #b91c1c; border-color: #fca5a5;' : ($entry['urgency'] === 'medium' ? 'background: #fef3c7; color: #b45309; border-color: #fcd34d;' : 'background: #dcfce7; color: #15803d; border-color: #86efac;') }}">
                                            {{ $entry['waited_minutes'] }}m
                                        </span>
                                    @endif
                                </div>

                                {{-- Seating Selector & Actions --}}
                                <div class="space-y-2 pt-2" style="border-top: 1px dashed #cbd5e1;">
                                    <div class="flex items-center gap-1.5">
                                        <select wire:model="assignments.{{ $entry['id'] }}"
                                            style="width: 100%; font-size: 0.75rem; border-radius: 0.75rem; border: 1.5px solid #cbd5e1; padding: 0.45rem 0.65rem; background: #ffffff; color: #0f172a; font-weight: 600;">
                                            <option value="">Pilih Meja Kosong...</option>
                                            @foreach ($this->availableTables as $avail)
                                                <option value="{{ $avail['id'] }}">
                                                    Meja {{ $avail['table_number'] }} ({{ $avail['capacity'] }} pax • {{ $avail['area_name'] ?? 'Area' }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="button"
                                            wire:click="seatQueueEntry({{ $entry['id'] }})"
                                            class="tsb-btn-success"
                                            style="padding: 0.45rem 0.85rem; font-size: 0.75rem; white-space: nowrap;">
                                            Seat
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between text-xs pt-0.5">
                                        <button type="button"
                                            wire:click="callQueueEntry({{ $entry['id'] }})"
                                            style="font-size: 0.75rem; font-weight: 700; color: #7c3aed; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                                            <span>📢</span>
                                            <span>Panggil Tamu</span>
                                        </button>

                                        <button type="button"
                                            wire:click="cancelQueueEntry({{ $entry['id'] }})"
                                            style="font-size: 0.75rem; font-weight: 600; color: #94a3b8; background: none; border: none; cursor: pointer;">
                                            Batalkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="padding: 2rem 1rem; text-align: center; border: 2px dashed #cbd5e1; border-radius: 1rem; background: #f8fafc;">
                                <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">🎉</span>
                                <p style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                                    Semua antrean telah terlayani!
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- ───────────────────────────────────────────────────────────── --}}
    {{-- SCOPED CSS SYSTEM: DEFINITE CONTRAST, COLORS & TACTILE DEPTH  --}}
    {{-- ───────────────────────────────────────────────────────────── --}}
    <style>
        /* Base Container */
        .tsb-wrapper {
            background-color: #f8fafc;
            border-radius: 1.5rem;
            padding: 0.75rem;
        }
        .dark .tsb-wrapper {
            background-color: #0b0f19;
        }

        /* Glass Header / Top Bar */
        .tsb-topbar {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .tsb-topbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dark .tsb-topbar {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
        }

        .tsb-topbar-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
            flex-shrink: 0;
        }

        .tsb-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .dark .tsb-title {
            color: #ffffff;
        }

        .tsb-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.2rem;
        }
        .dark .tsb-subtitle {
            color: #94a3b8;
        }

        .tsb-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 800;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .dark .tsb-live-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .tsb-pulse-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: tsb-pulse 1.8s infinite;
        }
        @keyframes tsb-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* Header Buttons */
        .tsb-header-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.9rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1.5px solid transparent;
        }
        .tsb-header-btn:active {
            transform: scale(0.97);
        }

        .tsb-btn-refresh {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .tsb-btn-refresh:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .dark .tsb-btn-refresh {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }
        .dark .tsb-btn-refresh:hover {
            background: #334155;
            color: #ffffff;
        }

        .tsb-btn-clean-all {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border-color: #d97706;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
        }
        .tsb-btn-clean-all:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            box-shadow: 0 6px 16px rgba(217, 119, 6, 0.45);
        }

        .tsb-btn-queue-link {
            background: #eef2ff;
            color: #4338ca;
            border-color: #c7d2fe;
            box-shadow: 0 1px 3px rgba(67, 56, 202, 0.08);
        }
        .tsb-btn-queue-link:hover {
            background: #e0e7ff;
            color: #3730a3;
            border-color: #a5b4fc;
        }
        .dark .tsb-btn-queue-link {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.35);
        }

        /* ───────────────────────────────────────────────────────────── */
        /* KPI METRIC TABS GRID                                          */
        /* ───────────────────────────────────────────────────────────── */
        .tsb-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .tsb-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .tsb-kpi-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        }

        .tsb-kpi-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0.95rem 1rem 0.85rem;
            border-radius: 1.15rem;
            border-width: 2px;
            border-style: solid;
            text-align: left;
            cursor: pointer;
            user-select: none;
            overflow: hidden;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .tsb-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }
        .tsb-kpi-card:active {
            transform: translateY(0);
        }

        .tsb-kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .tsb-kpi-label {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .tsb-kpi-tag {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.15rem 0.45rem;
            border-radius: 0.45rem;
            white-space: nowrap;
        }
        .tsb-kpi-count-wrap {
            margin-top: 0.65rem;
            display: flex;
            align-items: baseline;
            gap: 0.3rem;
        }
        .tsb-kpi-num {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
        }
        .tsb-kpi-unit {
            font-size: 0.75rem;
            font-weight: 700;
        }
        .tsb-kpi-footer {
            margin-top: 0.45rem;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tsb-kpi-stripe {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        /* 1. All Tables */
        .tsb-kpi-all {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .tsb-kpi-all .tsb-kpi-tag { background: #f1f5f9; color: #475569; }
        .tsb-kpi-all .tsb-kpi-num { color: #0f172a; }
        .tsb-kpi-all .tsb-kpi-unit { color: #94a3b8; }
        .tsb-kpi-all .tsb-kpi-footer { color: #64748b; }
        .tsb-kpi-all .tsb-kpi-stripe { background: #64748b; }

        .tsb-kpi-all.active {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            border-color: #0f172a !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.35) !important;
            transform: translateY(-3px);
        }
        .tsb-kpi-all.active .tsb-kpi-label,
        .tsb-kpi-all.active .tsb-kpi-num,
        .tsb-kpi-all.active .tsb-kpi-unit,
        .tsb-kpi-all.active .tsb-kpi-footer { color: #ffffff !important; }
        .tsb-kpi-all.active .tsb-kpi-tag { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
        .tsb-kpi-all.active .tsb-kpi-stripe { background: #f59e0b; }

        /* 2. Available (Kosong) */
        .tsb-kpi-available {
            background: #ecfdf5;
            border-color: #86efac;
            color: #065f46;
        }
        .tsb-kpi-available .tsb-kpi-tag { background: #dcfce7; color: #166534; }
        .tsb-kpi-available .tsb-kpi-num { color: #059669; }
        .tsb-kpi-available .tsb-kpi-unit { color: #10b981; }
        .tsb-kpi-available .tsb-kpi-footer { color: #047857; }
        .tsb-kpi-available .tsb-kpi-stripe { background: #10b981; }

        .tsb-kpi-available.active {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            border-color: #047857 !important;
            box-shadow: 0 10px 22px rgba(5, 150, 105, 0.4) !important;
            transform: translateY(-3px);
        }
        .tsb-kpi-available.active .tsb-kpi-label,
        .tsb-kpi-available.active .tsb-kpi-num,
        .tsb-kpi-available.active .tsb-kpi-unit,
        .tsb-kpi-available.active .tsb-kpi-footer { color: #ffffff !important; }
        .tsb-kpi-available.active .tsb-kpi-tag { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
        .tsb-kpi-available.active .tsb-kpi-stripe { background: #34d399; }

        /* 3. Occupied (Terisi) */
        .tsb-kpi-occupied {
            background: #fff1f2;
            border-color: #fca5a5;
            color: #9f1239;
        }
        .tsb-kpi-occupied .tsb-kpi-tag { background: #ffe4e6; color: #9f1239; }
        .tsb-kpi-occupied .tsb-kpi-num { color: #e11d48; }
        .tsb-kpi-occupied .tsb-kpi-unit { color: #f43f5e; }
        .tsb-kpi-occupied .tsb-kpi-footer { color: #be123c; }
        .tsb-kpi-occupied .tsb-kpi-stripe { background: #f43f5e; }

        .tsb-kpi-occupied.active {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
            border-color: #be123c !important;
            box-shadow: 0 10px 22px rgba(225, 29, 72, 0.4) !important;
            transform: translateY(-3px);
        }
        .tsb-kpi-occupied.active .tsb-kpi-label,
        .tsb-kpi-occupied.active .tsb-kpi-num,
        .tsb-kpi-occupied.active .tsb-kpi-unit,
        .tsb-kpi-occupied.active .tsb-kpi-footer { color: #ffffff !important; }
        .tsb-kpi-occupied.active .tsb-kpi-tag { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
        .tsb-kpi-occupied.active .tsb-kpi-stripe { background: #fb7185; }

        /* 4. Reserved */
        .tsb-kpi-reserved {
            background: #faf5ff;
            border-color: #d8b4fe;
            color: #581c87;
        }
        .tsb-kpi-reserved .tsb-kpi-tag { background: #f3e8ff; color: #6b21a8; }
        .tsb-kpi-reserved .tsb-kpi-num { color: #7c3aed; }
        .tsb-kpi-reserved .tsb-kpi-unit { color: #8b5cf6; }
        .tsb-kpi-reserved .tsb-kpi-footer { color: #6d28d9; }
        .tsb-kpi-reserved .tsb-kpi-stripe { background: #8b5cf6; }

        .tsb-kpi-reserved.active {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
            border-color: #6d28d9 !important;
            box-shadow: 0 10px 22px rgba(124, 58, 237, 0.4) !important;
            transform: translateY(-3px);
        }
        .tsb-kpi-reserved.active .tsb-kpi-label,
        .tsb-kpi-reserved.active .tsb-kpi-num,
        .tsb-kpi-reserved.active .tsb-kpi-unit,
        .tsb-kpi-reserved.active .tsb-kpi-footer { color: #ffffff !important; }
        .tsb-kpi-reserved.active .tsb-kpi-tag { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
        .tsb-kpi-reserved.active .tsb-kpi-stripe { background: #c084fc; }

        /* 5. Cleaning */
        .tsb-kpi-cleaning {
            background: #fffbeb;
            border-color: #fcd34d;
            color: #92400e;
        }
        .tsb-kpi-cleaning .tsb-kpi-tag { background: #fef3c7; color: #92400e; }
        .tsb-kpi-cleaning .tsb-kpi-num { color: #d97706; }
        .tsb-kpi-cleaning .tsb-kpi-unit { color: #f59e0b; }
        .tsb-kpi-cleaning .tsb-kpi-footer { color: #b45309; }
        .tsb-kpi-cleaning .tsb-kpi-stripe { background: #f59e0b; }

        .tsb-kpi-cleaning.active {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
            border-color: #b45309 !important;
            box-shadow: 0 10px 22px rgba(217, 119, 6, 0.4) !important;
            transform: translateY(-3px);
        }
        .tsb-kpi-cleaning.active .tsb-kpi-label,
        .tsb-kpi-cleaning.active .tsb-kpi-num,
        .tsb-kpi-cleaning.active .tsb-kpi-unit,
        .tsb-kpi-cleaning.active .tsb-kpi-footer { color: #ffffff !important; }
        .tsb-kpi-cleaning.active .tsb-kpi-tag { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
        .tsb-kpi-cleaning.active .tsb-kpi-stripe { background: #fde047; }

        /* 6. Queue */
        .tsb-kpi-queue {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1e40af;
        }
        .tsb-kpi-queue .tsb-kpi-tag { background: #dbeafe; color: #1e40af; }
        .tsb-kpi-queue .tsb-kpi-num { color: #2563eb; }
        .tsb-kpi-queue .tsb-kpi-unit { color: #3b82f6; }
        .tsb-kpi-queue .tsb-kpi-footer { color: #1d4ed8; }
        .tsb-kpi-queue .tsb-kpi-stripe { background: #3b82f6; }

        /* Dark Mode KPI Overrides */
        .dark .tsb-kpi-all { background: #1e293b; border-color: #334155; color: #f8fafc; }
        .dark .tsb-kpi-all .tsb-kpi-num { color: #ffffff; }
        .dark .tsb-kpi-all .tsb-kpi-tag { background: #334155; color: #cbd5e1; }

        .dark .tsb-kpi-available { background: rgba(6, 78, 59, 0.25); border-color: rgba(5, 150, 105, 0.5); color: #6ee7b7; }
        .dark .tsb-kpi-available .tsb-kpi-num { color: #34d399; }
        .dark .tsb-kpi-available .tsb-kpi-tag { background: rgba(6, 78, 59, 0.5); color: #a7f3d0; }

        .dark .tsb-kpi-occupied { background: rgba(136, 19, 55, 0.25); border-color: rgba(225, 29, 72, 0.5); color: #fda4af; }
        .dark .tsb-kpi-occupied .tsb-kpi-num { color: #fb7185; }
        .dark .tsb-kpi-occupied .tsb-kpi-tag { background: rgba(136, 19, 55, 0.5); color: #fecdd3; }

        .dark .tsb-kpi-reserved { background: rgba(76, 29, 149, 0.25); border-color: rgba(124, 58, 237, 0.5); color: #d8b4fe; }
        .dark .tsb-kpi-reserved .tsb-kpi-num { color: #c084fc; }
        .dark .tsb-kpi-reserved .tsb-kpi-tag { background: rgba(76, 29, 149, 0.5); color: #e9d5ff; }

        .dark .tsb-kpi-cleaning { background: rgba(120, 53, 15, 0.25); border-color: rgba(217, 119, 6, 0.5); color: #fde68a; }
        .dark .tsb-kpi-cleaning .tsb-kpi-num { color: #fbbf24; }
        .dark .tsb-kpi-cleaning .tsb-kpi-tag { background: rgba(120, 53, 15, 0.5); color: #fef3c7; }

        .dark .tsb-kpi-queue { background: rgba(30, 58, 138, 0.25); border-color: rgba(37, 99, 235, 0.5); color: #93c5fd; }
        .dark .tsb-kpi-queue .tsb-kpi-num { color: #60a5fa; }
        .dark .tsb-kpi-queue .tsb-kpi-tag { background: rgba(30, 58, 138, 0.5); color: #bfdbfe; }

        /* ───────────────────────────────────────────────────────────── */
        /* AREA SELECTOR BAR                                             */
        /* ───────────────────────────────────────────────────────────── */
        .tsb-area-bar {
            background: #e2e8f0;
            border: 1.5px solid #cbd5e1;
            border-radius: 1.15rem;
            padding: 0.5rem 0.65rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .dark .tsb-area-bar {
            background: #1e293b;
            border-color: #334155;
        }

        .tsb-area-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            padding: 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .dark .tsb-area-label {
            color: #94a3b8;
        }

        .tsb-area-pill {
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }
        .tsb-area-pill:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
        .tsb-area-pill.active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.3) !important;
            transform: translateY(-1px);
        }
        .dark .tsb-area-pill {
            background: #0f172a;
            color: #cbd5e1;
            border-color: #334155;
        }
        .dark .tsb-area-pill:hover {
            background: #1e293b;
            color: #ffffff;
        }
        .dark .tsb-area-pill.active {
            background: #3b82f6 !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
        }

        /* ───────────────────────────────────────────────────────────── */
        /* TABLE GRID & CARDS                                            */
        /* ───────────────────────────────────────────────────────────── */
        .tsb-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .tsb-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .tsb-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 1440px) {
            .tsb-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .tsb-card {
            background: #ffffff;
            border-radius: 1.25rem;
            border-width: 2px;
            border-style: solid;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            position: relative;
        }
        .tsb-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.12);
        }
        .dark .tsb-card {
            background: #1e293b;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .tsb-card-available { border-color: #10b981; }
        .tsb-card-occupied { border-color: #f43f5e; }
        .tsb-card-reserved { border-color: #8b5cf6; }
        .tsb-card-cleaning { border-color: #f59e0b; }

        .dark .tsb-card-available { border-color: #059669; }
        .dark .tsb-card-occupied { border-color: #e11d48; }
        .dark .tsb-card-reserved { border-color: #7c3aed; }
        .dark .tsb-card-cleaning { border-color: #d97706; }

        .tsb-card-stripe {
            height: 6px;
            width: 100%;
        }
        .tsb-card-stripe-available { background: linear-gradient(90deg, #10b981, #34d399); }
        .tsb-card-stripe-occupied { background: linear-gradient(90deg, #f43f5e, #fb7185); }
        .tsb-card-stripe-reserved { background: linear-gradient(90deg, #8b5cf6, #c084fc); }
        .tsb-card-stripe-cleaning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

        .tsb-card-body {
            padding: 1.1rem 1.15rem 0.95rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            gap: 0.95rem;
        }

        /* 3D Puck */
        .tsb-puck {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }
        .tsb-card:hover .tsb-puck {
            transform: scale(1.05);
        }
        .tsb-puck-tag {
            font-size: 0.55rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            line-height: 1;
            opacity: 0.9;
        }
        .tsb-puck-num {
            font-size: 1.35rem;
            font-weight: 900;
            line-height: 1;
            margin-top: 0.15rem;
            letter-spacing: -0.02em;
        }

        .tsb-puck-available { background: linear-gradient(135deg, #10b981 0%, #047857 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
        .tsb-puck-occupied { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); box-shadow: 0 4px 12px rgba(244, 63, 94, 0.4); }
        .tsb-puck-reserved { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        .tsb-puck-cleaning { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4); }

        /* Status Badge Pill */
        .tsb-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            border-width: 1.5px;
            border-style: solid;
            white-space: nowrap;
        }
        .tsb-badge-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 9999px;
        }
        .tsb-badge-available { background: #dcfce7; color: #15803d; border-color: #86efac; }
        .tsb-badge-available .tsb-badge-dot { background: #16a34a; }

        .tsb-badge-occupied { background: #ffe4e6; color: #be123c; border-color: #fca5a5; }
        .tsb-badge-occupied .tsb-badge-dot { background: #e11d48; animation: tsb-pulse 1.5s infinite; }

        .tsb-badge-reserved { background: #f3e8ff; color: #6d28d9; border-color: #d8b4fe; }
        .tsb-badge-reserved .tsb-badge-dot { background: #7c3aed; }

        .tsb-badge-cleaning { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
        .tsb-badge-cleaning .tsb-badge-dot { background: #d97706; }

        .dark .tsb-badge-available { background: rgba(6, 78, 59, 0.4); color: #6ee7b7; border-color: #059669; }
        .dark .tsb-badge-occupied { background: rgba(136, 19, 55, 0.4); color: #fda4af; border-color: #e11d48; }
        .dark .tsb-badge-reserved { background: rgba(76, 29, 149, 0.4); color: #d8b4fe; border-color: #7c3aed; }
        .dark .tsb-badge-cleaning { background: rgba(120, 53, 15, 0.4); color: #fde68a; border-color: #d97706; }

        /* Table Info Box */
        .tsb-box {
            border-radius: 0.875rem;
            padding: 0.75rem 0.85rem;
            border-width: 1.5px;
            border-style: solid;
            font-size: 0.75rem;
        }
        .tsb-box-available {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }
        .tsb-box-occupied {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }
        .tsb-box-reserved {
            background: #faf5ff;
            border-color: #e9d5ff;
            color: #581c87;
        }
        .tsb-box-cleaning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .dark .tsb-box-available { background: rgba(6, 78, 59, 0.25); border-color: rgba(5, 150, 105, 0.4); color: #a7f3d0; }
        .dark .tsb-box-occupied { background: rgba(136, 19, 55, 0.25); border-color: rgba(225, 29, 72, 0.4); color: #fecdd3; }
        .dark .tsb-box-reserved { background: rgba(76, 29, 149, 0.25); border-color: rgba(124, 58, 237, 0.4); color: #e9d5ff; }
        .dark .tsb-box-cleaning { background: rgba(120, 53, 15, 0.25); border-color: rgba(217, 119, 6, 0.4); color: #fef3c7; }

        /* Table Footer Actions */
        .tsb-card-footer {
            padding: 0.75rem 0.95rem;
            background: #f8fafc;
            border-top: 1.5px solid #e2e8f0;
        }
        .dark .tsb-card-footer {
            background: #0f172a;
            border-top-color: #334155;
        }

        /* Buttons inside Table Card */
        .tsb-btn-primary {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tsb-btn-primary:hover {
            background: linear-gradient(135deg, #c2410c 0%, #9a3412 100%);
            box-shadow: 0 6px 16px rgba(234, 88, 12, 0.4);
        }
        .tsb-btn-primary:active {
            transform: scale(0.97);
        }

        .tsb-btn-success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tsb-btn-success:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        }

        .tsb-btn-danger {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
            border: none;
            cursor: pointer;
        }

        .tsb-btn-purple {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
            border: none;
            cursor: pointer;
        }

        .tsb-btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 0.6rem 0.75rem;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .tsb-btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .dark .tsb-btn-secondary {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }

        /* Empty State Card */
        .tsb-empty-card {
            background: #ffffff;
            border: 2.5px dashed #cbd5e1;
            border-radius: 1.5rem;
            padding: 3.5rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }
        .dark .tsb-empty-card {
            background: #1e293b;
            border-color: #475569;
        }
        .tsb-empty-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            background: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .dark .tsb-empty-icon {
            background: #0f172a;
            color: #94a3b8;
        }

        /* Host Stand & Queue Tickets */
        .tsb-host-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 1.35rem;
            padding: 1.25rem;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }
        .dark .tsb-host-card {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .tsb-form-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
        }
        .dark .tsb-form-box {
            background: #0f172a;
            border-color: #334155;
        }

        .tsb-pax-btn {
            padding: 0.5rem;
            border-radius: 0.65rem;
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            transition: all 0.15s ease;
        }
        .tsb-pax-btn.active {
            background: linear-gradient(135deg, #ea580c, #c2410c) !important;
            color: #ffffff !important;
            border-color: #c2410c !important;
            box-shadow: 0 3px 8px rgba(234, 88, 12, 0.35) !important;
        }
        .dark .tsb-pax-btn {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }

        .tsb-ticket {
            background: #ffffff;
            border-radius: 1rem;
            border-width: 2px;
            border-style: solid;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .dark .tsb-ticket {
            background: #0f172a;
        }
        .tsb-ticket-low { border-color: #10b981; }
        .tsb-ticket-med { border-color: #f59e0b; }
        .tsb-ticket-high { border-color: #ef4444; box-shadow: 0 0 12px rgba(239, 68, 68, 0.25); }

        /* ───────────────────────────────────────────────────────────── */
        /* URGENT WAITER CALL BANNER & CHIPS                             */
        /* ───────────────────────────────────────────────────────────── */
        .tsb-waiter-banner {
            background: #ffffff;
            border: 2px solid #f43f5e;
            border-radius: 1.25rem;
            padding: 1.1rem 1.35rem;
            box-shadow: 0 4px 20px -2px rgba(225, 29, 72, 0.18), 0 2px 6px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        @media (min-width: 900px) {
            .tsb-waiter-banner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dark .tsb-waiter-banner {
            background: #1e293b;
            border-color: #f43f5e;
            box-shadow: 0 4px 24px -2px rgba(225, 29, 72, 0.35);
        }

        .tsb-waiter-banner-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .tsb-waiter-icon {
            width: 2.85rem;
            height: 2.85rem;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
            flex-shrink: 0;
            animation: tsb-bell-ring 2.2s infinite ease-in-out;
        }
        @keyframes tsb-bell-ring {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-14deg); }
            20%, 40% { transform: rotate(14deg); }
            50% { transform: rotate(0deg); }
        }

        .tsb-waiter-title-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .tsb-waiter-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #9f1239;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .dark .tsb-waiter-title {
            color: #fda4af;
        }

        .tsb-waiter-pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 800;
            background: #ffe4e6;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .dark .tsb-waiter-pulse-badge {
            background: rgba(225, 29, 72, 0.2);
            color: #fda4af;
            border-color: rgba(225, 29, 72, 0.4);
        }

        .tsb-waiter-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .dark .tsb-waiter-subtitle {
            color: #94a3b8;
        }

        .tsb-waiter-chips-wrap {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .tsb-waiter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: #fff1f2;
            border: 1.5px solid #fecdd3;
            padding: 0.45rem 0.65rem 0.45rem 0.85rem;
            border-radius: 0.95rem;
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.08);
            transition: all 0.15s ease;
        }
        .dark .tsb-waiter-chip {
            background: rgba(225, 29, 72, 0.12);
            border-color: rgba(225, 29, 72, 0.35);
        }

        .tsb-waiter-chip-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .tsb-waiter-chip-head {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tsb-waiter-puck {
            font-size: 0.85rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .dark .tsb-waiter-puck {
            color: #ffffff;
        }

        .tsb-waiter-reason-tag {
            font-size: 0.7rem;
            font-weight: 800;
            color: #be123c;
            background: #ffffff;
            padding: 0.1rem 0.45rem;
            border-radius: 0.45rem;
            border: 1px solid #fecdd3;
        }
        .dark .tsb-waiter-reason-tag {
            color: #fda4af;
            background: #1e293b;
            border-color: rgba(225, 29, 72, 0.4);
        }

        .tsb-waiter-chip-sub {
            font-size: 0.68rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .dark .tsb-waiter-chip-sub {
            color: #94a3b8;
        }

        .tsb-waiter-note-snippet {
            color: #b45309;
            font-style: italic;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dark .tsb-waiter-note-snippet {
            color: #fde68a;
        }

        .tsb-btn-waiter-dismiss {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.45rem 0.75rem;
            border-radius: 0.65rem;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .tsb-btn-waiter-dismiss:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4);
            transform: translateY(-1px);
        }
        .tsb-btn-waiter-dismiss:active {
            transform: translateY(0);
        }

        /* ───────────────────────────────────────────────────────────── */
        /* TABLE CARD WAITER ALERT                                       */
        /* ───────────────────────────────────────────────────────────── */
        .tsb-card-waiter-alert {
            background: #fff1f2;
            border: 1.5px solid #f43f5e;
            border-radius: 0.95rem;
            padding: 0.65rem 0.8rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 3px 10px rgba(244, 63, 94, 0.12);
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            animation: tsb-card-alert-glow 2.2s infinite alternate;
        }
        @keyframes tsb-card-alert-glow {
            0% { border-color: #f43f5e; box-shadow: 0 2px 8px rgba(244, 63, 94, 0.12); }
            100% { border-color: #e11d48; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.28); }
        }
        .dark .tsb-card-waiter-alert {
            background: rgba(225, 29, 72, 0.15);
            border-color: #f43f5e;
        }

        .tsb-card-waiter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem;
        }

        .tsb-card-waiter-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 900;
            color: #be123c;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .dark .tsb-card-waiter-badge {
            color: #fda4af;
        }

        .tsb-card-waiter-time {
            font-size: 0.68rem;
            font-weight: 700;
            color: #9f1239;
            background: #ffe4e6;
            padding: 0.1rem 0.4rem;
            border-radius: 0.4rem;
        }
        .dark .tsb-card-waiter-time {
            background: rgba(225, 29, 72, 0.3);
            color: #fecdd3;
        }

        .tsb-card-waiter-reason {
            font-size: 0.82rem;
            font-weight: 800;
            color: #0f172a;
        }
        .dark .tsb-card-waiter-reason {
            color: #ffffff;
        }

        .tsb-card-waiter-note {
            font-size: 0.7rem;
            color: #b45309;
            font-style: italic;
            background: #fffbeb;
            border: 1px dashed #fde68a;
            padding: 0.3rem 0.5rem;
            border-radius: 0.5rem;
            margin: 0.15rem 0;
        }
        .dark .tsb-card-waiter-note {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fef3c7;
        }

        .tsb-card-waiter-actions {
            margin-top: 0.2rem;
        }

        .tsb-btn-waiter-dismiss-card {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.45rem;
            border-radius: 0.65rem;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
            transition: all 0.15s ease;
        }
        .tsb-btn-waiter-dismiss-card:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.35);
        }
        .tsb-btn-waiter-dismiss-card:active {
            transform: scale(0.98);
        }

        /* Sound Button Styling */
        .tsb-btn-sound {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
            box-shadow: 0 1px 3px rgba(6, 95, 70, 0.08);
        }
        .tsb-btn-sound:hover {
            background: #d1fae5;
            color: #047857;
            border-color: #6ee7b7;
        }
        .tsb-btn-sound.muted {
            background: #f8fafc;
            color: #64748b;
            border-color: #cbd5e1;
        }
        .tsb-btn-sound.muted:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .dark .tsb-btn-sound {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.35);
        }
        .dark .tsb-btn-sound.muted {
            background: #1e293b;
            color: #94a3b8;
            border-color: #475569;
        }
    </style>

    {{-- Web Audio API Chime & Bell for POS Kasir --}}
    <script>
        function posAudio() {
            return {
                soundEnabled: true,
                audioCtx: null,

                init() {
                    const saved = localStorage.getItem('pos_sound_enabled');
                    if (saved !== null) {
                        this.soundEnabled = (saved === 'true');
                    }

                    window.addEventListener('play-pos-chime', (event) => {
                        if (this.soundEnabled) {
                            const detail = event.detail || {};
                            if (detail.type === 'waiter') {
                                this.playWaiterBell();
                            } else {
                                this.playOrderChime();
                            }
                        }
                    });
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    localStorage.setItem('pos_sound_enabled', this.soundEnabled);
                    if (this.soundEnabled) {
                        this.playOrderChime();
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

                playOrderChime() {
                    try {
                        const ctx = this.getAudioContext();
                        const now = ctx.currentTime;

                        const playTone = (freq, startTime, duration, gainVal) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(freq, startTime);
                            gain.gain.setValueAtTime(0.0001, startTime);
                            gain.gain.exponentialRampToValueAtTime(gainVal, startTime + 0.015);
                            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };

                        // Crisp order bell (High D5 -> A5)
                        playTone(587.33, now, 0.4, 0.3);
                        playTone(880, now + 0.14, 0.6, 0.35);
                    } catch (err) {
                        console.warn('POS Chime Error:', err);
                    }
                },

                playWaiterBell() {
                    try {
                        const ctx = this.getAudioContext();
                        const now = ctx.currentTime;

                        const playTone = (freq, startTime, duration, gainVal) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(freq, startTime);
                            gain.gain.setValueAtTime(0.0001, startTime);
                            gain.gain.exponentialRampToValueAtTime(gainVal, startTime + 0.01);
                            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };

                        // Triple rapid service bell for urgent waiter call
                        playTone(987.77, now, 0.25, 0.35);       // B5
                        playTone(987.77, now + 0.15, 0.25, 0.35); // B5
                        playTone(1318.5, now + 0.30, 0.5, 0.4);   // E6
                    } catch (err) {
                        console.warn('Waiter Bell Error:', err);
                    }
                }
            };
        }
    </script>
</x-filament::page>
