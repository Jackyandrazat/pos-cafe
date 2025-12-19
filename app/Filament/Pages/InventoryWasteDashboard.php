<?php

namespace App\Filament\Pages;

use App\Filament\Resources\IngredientWasteResource;
use App\Services\InventoryWasteReportService;
use App\Support\Feature;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryWasteDashboard extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.inventory-waste-dashboard';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Laporan Persediaan & Waste';

    public ?array $data = [];

    public array $summary = [];

    public Collection $rows;

    public function mount(): void
    {
        abort_unless(Feature::enabled('inventory_waste'), 403);

        $this->rows = collect();

        $this->form->fill([
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfDay()->toDateString(),
        ]);

        $this->loadReport();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('inventory_waste');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('logWaste')
                ->label('Catat Waste')
                ->icon('heroicon-o-plus')
                ->url(IngredientWasteResource::getUrl('create')),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->maxDate(fn (callable $get) => $get('end_date'))
                        ->required(),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->minDate(fn (callable $get) => $get('start_date'))
                        ->required(),
                ]),
        ])->statePath('data');
    }

    public function applyFilters(): void
    {
        $this->loadReport();
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'inventory-waste-' . now()->format('Ymd_His') . '.csv';

        $rows = $this->rows;
        $summary = $this->summary;

        return response()->streamDownload(function () use ($rows, $summary) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Ringkasan']);
            fputcsv($handle, ['Total Stok Masuk', $summary['total_stock_in'] ?? 0]);
            fputcsv($handle, ['Total Pemakaian', $summary['total_usage'] ?? 0]);
            fputcsv($handle, ['Total Waste', $summary['total_waste'] ?? 0]);
            fputcsv($handle, []);
            fputcsv($handle, ['Bahan', 'Stok Masuk', 'Pemakaian', 'Waste', 'Konsumsi', 'Variance', 'Sisa Stok', 'Biaya Waste', 'Persentase Waste']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['stock_in'],
                    $row['usage'],
                    $row['waste'],
                    $row['consumption'],
                    $row['variance'],
                    $row['current_stock'],
                    $row['waste_cost'],
                    $row['waste_ratio'] . '%',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    protected function loadReport(): void
    {
        $state = $this->form->getState();

        if (! $state || ! ($state['start_date'] ?? null)) {
            $this->summary = [];
            $this->rows = collect();

            return;
        }

        $start = Carbon::parse($state['start_date'])->startOfDay();
        $end = Carbon::parse($state['end_date'] ?? $state['start_date'])->endOfDay();

        $report = InventoryWasteReportService::generate($start, $end);

        $this->summary = $report['summary'];
        $this->rows = $report['rows'];
    }
}
