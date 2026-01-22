<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @php($member = $this->selectedMember)

        @if (! $member)
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Pilih customer untuk melihat status misi, badge, dan riwayat poin loyalti.
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Member terpilih</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-50">{{ $member['name'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member['phone'] ?? '-' }} • {{ $member['email'] ?? '-' }}</p>
                    </div>
                    @if ($this->memberDetailUrl)
                        <x-filament::button tag="a" href="{{ $this->memberDetailUrl }}" target="_blank" rel="noreferrer">
                            Lihat profil member
                        </x-filament::button>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Saldo Poin</p>
                        <p class="mt-2 text-2xl font-semibold text-primary-600 dark:text-primary-400">
                            {{ number_format($member['points']) }} pts
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Lifetime Value</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                            Rp {{ number_format($member['lifetime_value'] ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Order</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                            {{ number_format($member['orders_count'] ?? 0) }}x
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200/60 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Order Terakhir</p>
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-gray-50">
                            {{ $member['last_order_at'] ? \Carbon\Carbon::parse($member['last_order_at'])->translatedFormat('d M Y H:i') : '-' }}
                        </p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Misi</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-50">Gamified Loyalty Challenges</p>
                    </div>
                </div>

                @if (count($this->challengeCards))
                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($this->challengeCards as $card)
                            <div class="rounded-2xl border border-gray-200/60 bg-white/80 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['slug'] }}</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $card['name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card['description'] ?? '—' }}</p>
                                    </div>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700' => $card['status'] === 'rewarded',
                                        'bg-blue-100 text-blue-700' => $card['status'] === 'completed',
                                        'bg-amber-100 text-amber-700' => $card['status'] === 'in_progress',
                                        'bg-gray-100 text-gray-600' => $card['status'] === 'available',
                                    ])>
                                        @php($statusLabel = match ($card['status']) {
                                            'rewarded' => 'Badge diberikan',
                                            'completed' => 'Siap klaim',
                                            'in_progress' => 'Sedang berjalan',
                                            default => 'Belum mulai',
                                        })
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $card['current'] }} / {{ $card['target'] }}</span>
                                        <span>{{ $card['percentage'] }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 rounded-full bg-gray-200/80 dark:bg-white/10">
                                        <div class="h-2 rounded-full bg-primary-500" style="width: {{ $card['percentage'] }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                    <span>Reward: {{ $card['badge']['name'] ?? 'Badge' }} ({{ $card['badge']['points'] }} pts)</span>
                                    <span>{{ $card['window'] ?? 'Tanpa batas waktu' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Member belum memiliki progres misi aktif.</p>
                @endif
            </x-filament::section>

            @if (count($this->recentBadges))
                <x-filament::section>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Badge & Poin Terbaru</p>
                    <div class="mt-4 space-y-3">
                        @foreach ($this->recentBadges as $badge)
                            <div class="rounded-2xl border border-gray-200/60 bg-white/80 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-base font-semibold text-gray-900 dark:text-gray-50">{{ $badge['badge_name'] ?? 'Badge' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $badge['challenge'] ?? 'Challenge' }} • {{ $badge['badge_code'] ?? '-' }}</p>
                                    </div>
                                    <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                        <p class="font-semibold text-primary-600 dark:text-primary-400">+{{ $badge['points_awarded'] }} pts</p>
                                        <p>{{ $badge['awarded_at'] ? \Carbon\Carbon::parse($badge['awarded_at'])->diffForHumans() : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif
        @endif
    </div>
</x-filament-panels::page>
