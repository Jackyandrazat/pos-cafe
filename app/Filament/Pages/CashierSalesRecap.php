<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashierSalesRecap extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Rekap Penjualan';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Rekap Penjualan Kasir';

    protected static string $view = 'filament.pages.cashier-sales-recap';

    public ?array $filters = [
        'timeframe' => 'daily',
        'date' => null,
        'range_start' => null,
        'range_end' => null,
    ];

    public function mount(): void
    {
        $today = now()->toDateString();

        $this->filters = array_merge([
            'timeframe' => 'daily',
            'date' => $today,
            'range_start' => null,
            'range_end' => null,
        ], $this->filters ?? []);

        $this->filters['date'] ??= $today;

        if (! $this->filters['range_start'] || ! $this->filters['range_end']) {
            [$start, $end] = $this->defaultRangeFor(
                $this->filters['timeframe'] ?? 'daily',
                Carbon::parse($this->filters['date'])
            );

            $this->filters['range_start'] ??= $start->toDateString();
            $this->filters['range_end'] ??= $end->toDateString();
        }

        $this->form->fill($this->filters);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'owner', 'superadmin', 'kasir']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns([
                        'sm' => 2,
                        'md' => 4,
                        'lg' => 6,
                    ])
                    ->schema([
                        Forms\Components\Select::make('timeframe')
                            ->label('Rentang Waktu')
                            ->options([
                                'daily' => 'Per Hari',
                                'weekly' => 'Per Minggu (7 hari)',
                                'monthly' => 'Per Bulan (30 hari)',
                                'custom' => 'Custom Range',
                            ])
                            ->default('daily')
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, ?string $state): void {
                                $this->resetRangeFields($state ?? 'daily', $set, $get);
                                $this->resetTable();
                            })
                            ->columnSpan(['lg' => 2, 'md' => 2, 'sm' => 2]),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->visible(fn (Get $get) => $get('timeframe') === 'daily')
                            ->default(now()->toDateString())
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->maxDate(now())
                            ->live()
                            ->afterStateUpdated(fn () => $this->resetTable())
                            ->columnSpan(['lg' => 2, 'md' => 2, 'sm' => 2]),
                        Forms\Components\DatePicker::make('range_start')
                            ->label(fn (Get $get) => match ($get('timeframe')) {
                                'weekly' => 'Mulai Minggu',
                                'monthly' => 'Mulai Periode',
                                'custom' => 'Mulai Rentang',
                                default => 'Mulai',
                            })
                            ->visible(fn (Get $get) => $get('timeframe') !== 'daily')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->maxDate(now())
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, ?string $state): void {
                                if (! $state) {
                                    return;
                                }

                                $this->applyCustomRangeFromStart($state, $set, $get);
                                $this->resetTable();
                            })
                            ->columnSpan(['lg' => 2, 'md' => 2, 'sm' => 2]),
                        Forms\Components\DatePicker::make('range_end')
                            ->label(fn (Get $get) => match ($get('timeframe')) {
                                'weekly' => 'Akhir Minggu',
                                'monthly' => 'Akhir Periode',
                                'custom' => 'Akhir Rentang',
                                default => 'Akhir',
                            })
                            ->visible(fn (Get $get) => $get('timeframe') !== 'daily')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->maxDate(now())
                            ->minDate(fn (Get $get) => $get('range_start'))
                            ->disabled(fn (Get $get) => $get('timeframe') !== 'custom')
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, ?string $state): void {
                                $this->ensureCustomRangeEnd($set, $get, $state);
                                $this->resetTable();
                            })
                            ->columnSpan(['lg' => 2, 'md' => 2, 'sm' => 2]),
                    ]),
            ])
            ->statePath('filters');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Payment::query()->with(['order.table', 'order.user', 'shift.user']))
            ->modifyQueryUsing(function (Builder $query): void {
                $this->applyUserScope($query);
                $this->applyDateRange($query);
            })
            ->columns([
                TextColumn::make('order.id')
                    ->label('Order')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('order.customer_name')
                    ->label('Pelanggan')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? str($state)->upper() : '-'),
                TextColumn::make('amount_paid')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('change_return')
                    ->label('Kembalian')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cashier_name')
                    ->label('Kasir')
                    ->state(fn (Payment $record) => $this->resolveCashierName($record))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $subQuery) use ($search) {
                            $subQuery
                                ->whereHas('shift.user', fn ($relation) => $relation->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('order.user', fn ($relation) => $relation->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('payment_date')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->getStateUsing(fn (Payment $record) => $this->resolvePaymentDate($record)),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => $this->exportRecap()),
            ])
            ->emptyStateHeading('Belum ada transaksi dalam periode ini');
    }

    public function exportRecap(): StreamedResponse
    {
        $query = Payment::query()->with(['order.user', 'shift.user']);
        $this->applyUserScope($query);
        $this->applyDateRange($query);

        $filename = sprintf('rekap-penjualan-%s.csv', now()->format('YmdHis'));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment ID', 'Order ID', 'Pelanggan', 'Kasir', 'Metode', 'Total Bayar', 'Kembalian', 'Waktu Bayar']);

            $query->orderByRaw('COALESCE(payment_date, created_at)')->chunk(500, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
                    fputcsv($handle, [
                        $payment->id,
                        $payment->order?->id,
                        $payment->order?->customer_name,
                        $this->resolveCashierName($payment),
                        $payment->payment_method,
                        $payment->amount_paid,
                        $payment->change_return,
                        optional($this->resolvePaymentDate($payment))->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getCurrentRangeLabelProperty(): string
    {
        [$start, $end] = $this->resolveRange();

        return $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');
    }

    public function getOverallStatsProperty(): array
    {
        $query = Payment::query();
        $this->applyUserScope($query);
        $this->applyDateRange($query);

        $totalSales = (clone $query)->sum('amount_paid');
        $transactionCount = (clone $query)->count();

        return [
            'total_sales' => $totalSales,
            'transactions' => $transactionCount,
            'average' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
        ];
    }

    public function getCashierSummaryProperty(): array
    {
        [$start, $end] = $this->resolveRange();

        $query = Payment::query()
            ->leftJoin('shifts', 'payments.shift_id', '=', 'shifts.id')
            ->leftJoin('users as shift_users', 'shifts.user_id', '=', 'shift_users.id')
            ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('users as order_users', 'orders.user_id', '=', 'order_users.id')
            ->selectRaw('COALESCE(shift_users.id, order_users.id, 0) as cashier_id')
            ->selectRaw('COALESCE(shift_users.name, order_users.name, "Tidak diketahui") as cashier_name')
            ->selectRaw('SUM(payments.amount_paid) as total_sales')
            ->selectRaw('COUNT(payments.id) as transactions_count')
            ->whereBetween(DB::raw('COALESCE(payments.payment_date, payments.created_at)'), [$start->toDateTimeString(), $end->toDateTimeString()]);

        if (! $this->userCanSeeAll()) {
            $query->where(function ($subQuery) {
                $userId = Auth::id();
                $subQuery
                    ->where('shift_users.id', $userId)
                    ->orWhere('order_users.id', $userId);
            });
        }

        return $query
            ->groupBy('cashier_id', 'cashier_name')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->cashier_name,
                'total' => (float) $row->total_sales,
                'transactions' => (int) $row->transactions_count,
            ])
            ->toArray();
    }

    protected function applyUserScope(Builder $query): void
    {
        if ($this->userCanSeeAll()) {
            return;
        }

        $userId = Auth::id();

        $query->where(function ($subQuery) use ($userId) {
            $subQuery
                ->whereHas('shift', fn ($shiftQuery) => $shiftQuery->where('user_id', $userId))
                ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('user_id', $userId));
        });
    }

    protected function applyDateRange(Builder $query): void
    {
        [$start, $end] = $this->resolveRange();

        $query->whereBetween(DB::raw('COALESCE(payments.payment_date, payments.created_at)'), [
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ]);
    }

    protected function resolveRange(): array
    {
        $timeframe = Arr::get($this->filters, 'timeframe', 'daily');

        if ($timeframe === 'daily') {
            $referenceDate = Arr::get($this->filters, 'date') ?: now()->toDateString();
            $date = Carbon::parse($referenceDate);

            return [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ];
        }

        $startDate = Arr::get($this->filters, 'range_start');
        $endDate = Arr::get($this->filters, 'range_end');

        if (! $startDate) {
            [$defaultStart, $defaultEnd] = $this->defaultRangeFor($timeframe, Carbon::parse(Arr::get($this->filters, 'date') ?? now()));

            return [$defaultStart, $defaultEnd];
        }

        $start = Carbon::parse($startDate)->startOfDay();

        if (! $endDate) {
            [, $endFallback] = $this->customRangeFor($timeframe, $start->copy());

            return [$start, $endFallback];
        }

        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        return [$start, $end];
    }

    protected function resetRangeFields(string $timeframe, callable $set, callable $get): void
    {
        $reference = $get('date') ?: now()->toDateString();
        [$start, $end] = $this->defaultRangeFor($timeframe, Carbon::parse($reference));

        $set('range_start', $start->toDateString());
        $set('range_end', $end->toDateString());

        if ($timeframe === 'daily') {
            $set('date', $start->toDateString());
        }
    }

    protected function applyCustomRangeFromStart(string $startDate, callable $set, callable $get): void
    {
        $timeframe = $get('timeframe') ?? 'daily';

        if ($timeframe === 'daily') {
            return;
        }

        $start = Carbon::parse($startDate)->startOfDay();

        if (in_array($timeframe, ['weekly', 'monthly'], true)) {
            [$rangeStart, $rangeEnd] = $this->customRangeFor($timeframe, $start);

            $set('range_start', $rangeStart->toDateString());
            $set('range_end', $rangeEnd->toDateString());

            return;
        }

        if ($timeframe === 'custom') {
            $set('range_start', $start->toDateString());

            $end = $get('range_end');

            if ($end && Carbon::parse($end)->lt($start)) {
                $set('range_end', $start->toDateString());
            }
        }
    }

    protected function defaultRangeFor(string $timeframe, ?Carbon $referenceDate = null): array
    {
        $referenceDate ??= now();

        return match ($timeframe) {
            'weekly' => [
                $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->addDays(6)->endOfDay(),
            ],
            'monthly' => [
                $referenceDate->copy()->startOfMonth()->startOfDay(),
                $referenceDate->copy()->startOfMonth()->addDays(29)->endOfDay(),
            ],
            'custom' => [
                $referenceDate->copy()->startOfDay(),
                $referenceDate->copy()->endOfDay(),
            ],
            default => [
                $referenceDate->copy()->startOfDay(),
                $referenceDate->copy()->endOfDay(),
            ],
        };
    }

    protected function customRangeFor(string $timeframe, Carbon $startDate): array
    {
        return match ($timeframe) {
            'weekly' => [
                $startDate->copy()->startOfDay(),
                $startDate->copy()->startOfDay()->addDays(6)->endOfDay(),
            ],
            'monthly' => [
                $startDate->copy()->startOfDay(),
                $startDate->copy()->startOfDay()->addDays(29)->endOfDay(),
            ],
            'custom' => [
                $startDate->copy()->startOfDay(),
                $startDate->copy()->endOfDay(),
            ],
            default => [
                $startDate->copy()->startOfDay(),
                $startDate->copy()->endOfDay(),
            ],
        };
    }

    protected function ensureCustomRangeEnd(callable $set, callable $get, ?string $state): void
    {
        if (($get('timeframe') ?? 'daily') !== 'custom') {
            return;
        }

        $start = $get('range_start');

        if (! $start) {
            return;
        }

        $startDate = Carbon::parse($start)->startOfDay();

        if (! $state) {
            $set('range_end', $startDate->toDateString());

            return;
        }

        $endDate = Carbon::parse($state)->startOfDay();

        if ($endDate->lt($startDate)) {
            $set('range_end', $startDate->toDateString());
        }
    }

    protected function resolveCashierName(Payment $payment): string
    {
        return $payment->shift?->user?->name
            ?? $payment->order?->user?->name
            ?? 'Tidak diketahui';
    }

    protected function resolvePaymentDate(Payment $payment): ?Carbon
    {
        $date = $payment->payment_date ?? $payment->created_at;

        return $date ? Carbon::parse($date) : null;
    }

    protected function userCanSeeAll(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'owner', 'superadmin']);
    }
}
