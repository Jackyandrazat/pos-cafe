<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\CafeTable;
use App\Models\TableQueueEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TableStatusBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $title = 'Monitor Meja & Antrean';

    protected static string $view = 'filament.pages.table-status-board';

    public ?int $selectedArea = null;

    public array $tables = [];

    public array $queueEntries = [];

    public array $assignments = [];

    public array $statusOptions = [
        'available' => 'Kosong',
        'reserved' => 'Reserved',
        'occupied' => 'Terisi',
        'cleaning' => 'Cleaning',
    ];

    public function mount(): void
    {
        $this->selectedArea = Area::query()->where('status_enabled', true)->value('id');
        $this->refreshData();
    }

    public function updatedSelectedArea(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $this->tables = CafeTable::with('area')
            ->when($this->selectedArea, fn ($query) => $query->where('area_id', $this->selectedArea))
            ->orderBy('table_number')
            ->get()
            ->toArray();

        $this->queueEntries = TableQueueEntry::orderBy('check_in_at')->get()->toArray();
    }

    public function setStatus(int $tableId, string $status): void
    {
        $table = CafeTable::findOrFail($tableId);
        $table->update(['status' => $status]);

        Notification::make()->title("Status meja {$table->table_number} → " . ucfirst($status))->success()->send();
        $this->refreshData();
    }

    public function seatQueueEntry(int $entryId): void
    {
        $tableId = $this->assignments[$entryId] ?? null;

        if (! $tableId) {
            Notification::make()->title('Pilih meja terlebih dahulu.')->warning()->send();

            return;
        }

        $entry = TableQueueEntry::findOrFail($entryId);
        $table = CafeTable::findOrFail($tableId);

        $entry->update([
            'status' => 'seated',
            'assigned_table_id' => $table->id,
            'seated_at' => now(),
        ]);

        $table->update(['status' => 'occupied']);

        Notification::make()->title("{$entry->guest_name} diarahkan ke meja {$table->table_number}.")->success()->send();

        unset($this->assignments[$entryId]);
        $this->refreshData();
    }

    public function cancelQueueEntry(int $entryId): void
    {
        TableQueueEntry::where('id', $entryId)->update(['status' => 'cancelled']);
        $this->refreshData();
    }

    public function callQueueEntry(int $entryId): void
    {
        TableQueueEntry::where('id', $entryId)->update(['status' => 'called']);
        $this->refreshData();
    }

    public function getAreasProperty()
    {
        return Area::where('status_enabled', true)->orderBy('name')->get();
    }

    public function getAvailableTablesProperty()
    {
        return CafeTable::where('status', 'available')->orderBy('table_number')->get();
    }

    // Helper methods removed; always refresh from DB for consistency
}
