<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Area;
use App\Models\CafeTable;
use App\Models\Order;
use App\Models\TableQueueEntry;
use App\Support\Feature;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class TableStatusBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $title = 'Monitor Meja & Antrean';

    protected static string $view = 'filament.pages.table-status-board';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['admin', 'owner', 'kasir']);
    }

    /** Area yang dipilih; null = semua area. */
    public ?int $selectedArea = null;

    /** Filter status meja: all|available|occupied|reserved|cleaning */
    public string $statusFilter = 'all';

    /** Tab aktif pada layar kecil: tables|queue */
    public string $mobileTab = 'tables';

    public array $tables = [];

    public array $queueEntries = [];

    public array $assignments = [];

    public array $knownOrderIds = [];

    public array $knownWaiterCallTableIds = [];

    public bool $isFirstLoad = true;

    public array $stats = [
        'total' => 0,
        'available' => 0,
        'occupied' => 0,
        'reserved' => 0,
        'cleaning' => 0,
        'calling_waiter_count' => 0,
        'occupancy' => 0,
        'seats_total' => 0,
        'seats_used' => 0,
        'queue_waiting' => 0,
        'queue_guests' => 0,
        'queue_longest' => 0,
    ];

    /** Form cepat tambah antrean (host stand). */
    public string $newGuestName = '';

    public int $newPartySize = 2;

    public array $statusOptions = [
        'available' => 'Kosong',
        'occupied' => 'Terisi',
        'reserved' => 'Reserved',
        'cleaning' => 'Cleaning',
    ];

    public array $queueStatusLabels = [
        'waiting' => 'Menunggu',
        'called' => 'Dipanggil',
        'seated' => 'Sudah Duduk',
        'cancelled' => 'Batal',
    ];

    public function mount(): void
    {
        abort_unless(Feature::enabled('table_management'), 403);

        $this->refreshData();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('table_management');
    }

    // ─────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────

    public function refreshData(): void
    {
        $allTables = CafeTable::with('area')
            ->when($this->selectedArea, fn ($query) => $query->where('area_id', $this->selectedArea))
            ->orderByRaw('LENGTH(table_number) asc')
            ->orderBy('table_number')
            ->get();

        $ordersByTable = $this->activeOrdersByTable($allTables->pluck('id')->all());
        $seatedByTable = $this->seatedGuestsByTable($allTables->pluck('id')->all());

        $mapped = $allTables->map(function (CafeTable $table) use ($ordersByTable, $seatedByTable) {
            $order = $ordersByTable->get($table->id);
            $guest = $seatedByTable->get($table->id);

            $createOrderParams = ['table_id' => $table->id];
            if (!empty($guest?->guest_name)) {
                $createOrderParams['customer_name'] = $guest->guest_name;
            }

            $waitedCallMinutes = $table->waiter_called_at ? (int) $table->waiter_called_at->diffInMinutes(now()) : 0;

            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'status' => $table->status,
                'status_label' => $this->statusOptions[$table->status] ?? ucfirst((string) $table->status),
                'capacity' => (int) ($table->capacity ?? 0),
                'notes' => $table->notes,
                'area_name' => $table->area?->name,
                'calling_waiter' => (bool) $table->calling_waiter,
                'waiter_call_reason' => $table->waiter_call_reason,
                'waiter_call_reason_label' => $table->getWaiterCallReasonLabel(),
                'waiter_call_notes' => $table->waiter_call_notes,
                'waiter_called_at' => optional($table->waiter_called_at)->format('H:i'),
                'waiter_waited_minutes' => $waitedCallMinutes,
                'waiter_waited_text' => $this->humanMinutes($waitedCallMinutes),
                'guest_name' => $guest?->guest_name,
                'party_size' => $guest?->party_size,
                'order' => $order ? [
                    'id' => $order->id,
                    'status' => (string) $order->status,
                    'status_label' => OrderStatus::tryFrom((string) $order->status)?->label() ?? ucfirst((string) $order->status),
                    'total' => (float) $order->total_order,
                    'items_count' => (int) ($order->order_items_count ?? $order->items_count ?? 0),
                    'started_at' => optional($order->created_at)->format('H:i'),
                    'elapsed_minutes' => $order->created_at ? (int) $order->created_at->diffInMinutes(now()) : 0,
                    'elapsed_text' => $this->humanMinutes($order->created_at ? (int) $order->created_at->diffInMinutes(now()) : 0),
                    'edit_url' => \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order->id]),
                    'payment_url' => \App\Filament\Resources\PaymentResource::getUrl('create', ['order_id' => $order->id]),
                ] : null,
                'create_order_url' => \App\Filament\Resources\OrderResource::getUrl('create', $createOrderParams),
            ];
        });

        $this->stats = [
            'total' => $mapped->count(),
            'available' => $mapped->where('status', 'available')->count(),
            'occupied' => $mapped->where('status', 'occupied')->count(),
            'reserved' => $mapped->where('status', 'reserved')->count(),
            'cleaning' => $mapped->where('status', 'cleaning')->count(),
            'calling_waiter_count' => $mapped->where('calling_waiter', true)->count(),
            'occupancy' => $mapped->count() > 0
                ? (int) round($mapped->whereIn('status', ['occupied', 'reserved'])->count() / $mapped->count() * 100)
                : 0,
            'seats_total' => (int) $mapped->sum('capacity'),
            'seats_used' => (int) $mapped->where('status', 'occupied')->sum('capacity'),
        ];

        $this->tables = ($this->statusFilter === 'all'
            ? $mapped
            : $mapped->where('status', $this->statusFilter))
            ->values()
            ->all();

        // Cek apakah ada order baru atau panggilan pelayan baru untuk memicu lonceng suara
        $currentOrderIds = $ordersByTable->pluck('id')->all();
        $currentWaiterCallIds = $allTables->where('calling_waiter', true)->pluck('id')->all();

        if (! $this->isFirstLoad) {
            $newOrderIds = array_diff($currentOrderIds, $this->knownOrderIds);
            if (! empty($newOrderIds)) {
                $this->dispatch('play-pos-chime', type: 'order', count: count($newOrderIds));
            }

            $newWaiterCallIds = array_diff($currentWaiterCallIds, $this->knownWaiterCallTableIds);
            if (! empty($newWaiterCallIds)) {
                $this->dispatch('play-pos-chime', type: 'waiter', count: count($newWaiterCallIds));
            }
        } else {
            $this->isFirstLoad = false;
        }

        $this->knownOrderIds = $currentOrderIds;
        $this->knownWaiterCallTableIds = $currentWaiterCallIds;

        $this->loadQueue();
    }

    public function dismissWaiterCall(int $tableId): void
    {
        $table = CafeTable::findOrFail($tableId);
        $table->dismissWaiterCall();

        Notification::make()
            ->title("Panggilan Meja {$table->table_number} telah diselesaikan.")
            ->success()
            ->send();

        $this->refreshData();
    }

    protected function loadQueue(): void
    {
        $entries = TableQueueEntry::with('table')
            ->whereIn('status', ['waiting', 'called'])
            ->orderBy('check_in_at')
            ->get();

        $this->queueEntries = $entries->map(function (TableQueueEntry $entry) {
            $waited = $entry->check_in_at ? (int) $entry->check_in_at->diffInMinutes(now()) : 0;

            return [
                'id' => $entry->id,
                'guest_name' => $entry->guest_name,
                'party_size' => (int) $entry->party_size,
                'contact' => $entry->contact,
                'status' => $entry->status,
                'status_label' => $this->queueStatusLabels[$entry->status] ?? ucfirst((string) $entry->status),
                'notes' => $entry->notes,
                'estimated_wait_minutes' => $entry->estimated_wait_minutes,
                'check_in_time' => optional($entry->check_in_at)->format('H:i'),
                'waited_minutes' => $waited,
                'waited_text' => $this->humanMinutes($waited),
                'urgency' => $waited >= 30 ? 'high' : ($waited >= 15 ? 'medium' : 'low'),
            ];
        })->all();

        $this->stats['queue_waiting'] = count($this->queueEntries);
        $this->stats['queue_guests'] = (int) collect($this->queueEntries)->sum('party_size');
        $this->stats['queue_longest'] = (int) collect($this->queueEntries)->max('waited_minutes');
    }

    /** Order aktif (belum selesai/batal) terbaru per meja. */
    protected function activeOrdersByTable(array $tableIds): Collection
    {
        if (empty($tableIds)) {
            return collect();
        }

        return Order::query()
            ->withCount('order_items')
            ->whereIn('table_id', $tableIds)
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('table_id');
    }

    /** Tamu antrean yang sudah didudukkan hari ini, per meja. */
    protected function seatedGuestsByTable(array $tableIds): Collection
    {
        if (empty($tableIds)) {
            return collect();
        }

        return TableQueueEntry::query()
            ->where('status', 'seated')
            ->whereIn('assigned_table_id', $tableIds)
            ->whereDate('seated_at', today())
            ->orderByDesc('seated_at')
            ->get()
            ->keyBy('assigned_table_id');
    }

    protected function humanMinutes(int $minutes): string
    {
        if ($minutes < 1) {
            return 'baru saja';
        }

        if ($minutes < 60) {
            return "{$minutes} mnt";
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest > 0 ? "{$hours}j {$rest}m" : "{$hours} jam";
    }

    // ─────────────────────────────────────────────────────────────────
    // Filter & navigasi
    // ─────────────────────────────────────────────────────────────────

    public function selectArea($areaId = null): void
    {
        $this->selectedArea = $areaId !== null && $areaId !== '' ? (int) $areaId : null;
        $this->refreshData();
    }

    public function setStatusFilter(string $filter): void
    {
        $this->statusFilter = array_key_exists($filter, $this->statusOptions) ? $filter : 'all';
        $this->refreshData();
    }

    public function setMobileTab(string $tab): void
    {
        $this->mobileTab = in_array($tab, ['tables', 'queue'], true) ? $tab : 'tables';
    }

    // ─────────────────────────────────────────────────────────────────
    // Aksi meja
    // ─────────────────────────────────────────────────────────────────

    public function setStatus(int $tableId, string $status): void
    {
        if (! array_key_exists($status, $this->statusOptions)) {
            Notification::make()->title('Status meja tidak dikenal.')->danger()->send();

            return;
        }

        $table = CafeTable::findOrFail($tableId);

        if ($status === 'available' && $this->hasOpenOrder($table)) {
            Notification::make()
                ->title("Meja {$table->table_number} masih punya pesanan aktif.")
                ->body('Selesaikan atau batalkan pesanan lewat Kitchen Display / Kasir sebelum mengosongkan meja.')
                ->warning()
                ->send();

            return;
        }

        $table->update(['status' => $status]);

        Notification::make()
            ->title("Meja {$table->table_number} → {$this->statusOptions[$status]}")
            ->success()
            ->send();

        $this->refreshData();
    }

    /** Alur cepat kasir: tamu pulang → meja masuk status cleaning. */
    public function clearTable(int $tableId): void
    {
        $table = CafeTable::findOrFail($tableId);

        if ($this->hasOpenOrder($table)) {
            Notification::make()
                ->title("Meja {$table->table_number} masih punya pesanan aktif.")
                ->body('Tutup pesanannya dulu supaya tagihan tidak tertinggal.')
                ->warning()
                ->send();

            return;
        }

        $table->update(['status' => 'cleaning']);

        Notification::make()->title("Meja {$table->table_number} masuk daftar bersih-bersih.")->success()->send();

        $this->refreshData();
    }

    /** Selesai bersih-bersih untuk seluruh meja pada scope area aktif. */
    public function finishAllCleaning(): void
    {
        $count = CafeTable::query()
            ->when($this->selectedArea, fn ($query) => $query->where('area_id', $this->selectedArea))
            ->where('status', 'cleaning')
            ->update(['status' => 'available']);

        Notification::make()
            ->title($count > 0 ? "{$count} meja siap dipakai lagi." : 'Tidak ada meja berstatus cleaning.')
            ->success()
            ->send();

        $this->refreshData();
    }

    protected function hasOpenOrder(CafeTable $table): bool
    {
        return Order::query()
            ->where('table_id', $table->id)
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->exists();
    }

    // ─────────────────────────────────────────────────────────────────
    // Aksi antrean
    // ─────────────────────────────────────────────────────────────────

    public function addQueueEntry(): void
    {
        $name = trim($this->newGuestName) !== '' ? trim($this->newGuestName) : 'Walk-in';
        $size = max(1, (int) $this->newPartySize);

        TableQueueEntry::create([
            'guest_name' => $name,
            'party_size' => $size,
            'status' => 'waiting',
            'check_in_at' => now(),
        ]);

        Notification::make()->title("{$name} ({$size} pax) masuk antrean.")->success()->send();

        $this->newGuestName = '';
        $this->newPartySize = 2;

        $this->refreshData();
    }

    public function setPartySize(int $size): void
    {
        $this->newPartySize = max(1, $size);
    }

    public function seatQueueEntry(int $entryId, ?int $tableId = null): void
    {
        if (! $tableId && isset($this->assignments[$entryId]) && ! empty($this->assignments[$entryId])) {
            $tableId = (int) $this->assignments[$entryId];
        }

        if (! $tableId) {
            Notification::make()
                ->title('Pilih meja tujuan terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        $entry = TableQueueEntry::findOrFail($entryId);
        $table = CafeTable::findOrFail($tableId);

        if ($table->status !== 'available') {
            Notification::make()
                ->title("Meja {$table->table_number} sudah tidak kosong.")
                ->warning()
                ->send();

            $this->refreshData();

            return;
        }

        $entry->update([
            'status' => 'seated',
            'assigned_table_id' => $table->id,
            'seated_at' => now(),
        ]);

        $table->update(['status' => 'occupied']);

        $notification = Notification::make()
            ->title("{$entry->guest_name} duduk di meja {$table->table_number}.")
            ->success();

        if ($table->capacity > 0 && $entry->party_size > $table->capacity) {
            $notification->body("Catatan: {$entry->party_size} pax pada meja kapasitas {$table->capacity}, siapkan kursi tambahan.");
        }

        $notification->send();

        $this->refreshData();
    }

    public function callQueueEntry(int $entryId): void
    {
        $entry = TableQueueEntry::findOrFail($entryId);
        $entry->update(['status' => 'called']);

        Notification::make()->title("{$entry->guest_name} dipanggil.")->success()->send();

        $this->refreshData();
    }

    public function cancelQueueEntry(int $entryId): void
    {
        $entry = TableQueueEntry::findOrFail($entryId);
        $entry->update(['status' => 'cancelled']);

        Notification::make()->title("Antrean {$entry->guest_name} dibatalkan.")->send();

        $this->refreshData();
    }

    // ─────────────────────────────────────────────────────────────────
    // Computed
    // ─────────────────────────────────────────────────────────────────

    public function getAreasProperty()
    {
        return Area::where('status_enabled', true)->orderBy('name')->get();
    }

    public function getAreaCountsProperty(): array
    {
        return CafeTable::query()
            ->selectRaw('area_id, count(*) as count')
            ->groupBy('area_id')
            ->pluck('count', 'area_id')
            ->all();
    }

    public function getTotalAllTablesCountProperty(): int
    {
        return CafeTable::count();
    }

    /** Meja kosong pada scope area aktif, untuk sheet pemilihan meja. */
    public function getAvailableTablesProperty()
    {
        return CafeTable::with('area')
            ->where('status', 'available')
            ->when($this->selectedArea, fn ($query) => $query->where('area_id', $this->selectedArea))
            ->orderByRaw('LENGTH(table_number) asc')
            ->orderBy('table_number')
            ->get()
            ->map(fn (CafeTable $table) => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => (int) ($table->capacity ?? 0),
                'area_name' => $table->area?->name,
            ])
            ->all();
    }
}
