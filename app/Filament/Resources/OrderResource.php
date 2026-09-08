<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\CafeTable;
use App\Models\Order;
use App\Models\Product;
use App\Support\Feature;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use App\Filament\Exports\OrderExporter;
use Filament\Tables\Actions\BulkAction;
use App\Filament\Exports\ProductExporter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\OrderResource\Pages;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;
use App\Services\StockService;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Orderan';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['admin', 'owner', 'kasir']);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // =========================================================================
            // SEKSI 1: INFORMASI PESANAN (Tipe Order, Meja, Member, Nama Pelanggan)
            // =========================================================================
            Forms\Components\Section::make('Informasi Pesanan')
                ->description('Tentukan tipe pesanan, meja untuk dine in, dan data pelanggan.')
                ->icon('heroicon-o-shopping-bag')
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 4,
                ])
                ->schema([
                    Forms\Components\Select::make('order_type')
                        ->label('Tipe Order')
                        ->options([
                            'dine_in' => 'Dine In',
                            'take_away' => 'Take Away',
                            'delivery' => 'Delivery',
                        ])
                        ->default('dine_in')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('table_id')
                        ->label('Meja (Khusus Dine In)')
                        ->options(function (callable $get) {
                            $current = $get('table_id') ?: request()->query('table_id');

                            return CafeTable::with('area')
                                ->when($current, function ($query) use ($current) {
                                    $query->where(function ($q) use ($current) {
                                        $q->where('status', 'available')
                                          ->orWhere('id', $current);
                                    });
                                }, function ($query) {
                                    $query->where('status', 'available');
                                })
                                ->orderByRaw('LENGTH(table_number) asc')
                                ->orderBy('table_number')
                                ->get()
                                ->mapWithKeys(function ($t) {
                                    $area = $t->area?->name ? " • {$t->area->name}" : '';
                                    return [$t->id => "Meja {$t->table_number} ({$t->capacity} pax{$area})"];
                                })
                                ->all();
                        })
                        ->default(fn () => request()->query('table_id') ? (int) request()->query('table_id') : null)
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih meja...')
                        ->helperText('Menampilkan meja yang kosong/tersedia.')
                        ->nullable()
                        ->visible(fn () => Feature::enabled('table_management')),

                    Forms\Components\Select::make('customer_id')
                        ->label('Member Loyalty')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nama Member')->required(),
                            Forms\Components\TextInput::make('email')->label('Email')->email()->nullable(),
                            Forms\Components\TextInput::make('phone')->label('No. Telepon')->tel()->nullable(),
                        ])
                        ->placeholder('Pilih member (opsional)...')
                        ->helperText('Pilih member untuk poin loyalitas.')
                        ->visible(fn () => Feature::enabled('loyalty')),

                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nama Pelanggan / Tamu')
                        ->placeholder('Contoh: Budi, Meja 2')
                        ->default(fn () => request()->query('customer_name'))
                        ->nullable()
                        ->maxLength(100),
                ]),

            // =========================================================================
            // SEKSI 2: DISKON, PROMO & RINGKASAN PEMBAYARAN
            // =========================================================================
            Forms\Components\Section::make('Diskon, Promo & Kalkulasi Tagihan')
                ->description('Gunakan kode promo, gift card, atau diskon manual jika diperlukan.')
                ->icon('heroicon-o-ticket')
                ->collapsible()
                ->collapsed(fn (?Order $record) => ! ($record?->promotion_code || $record?->gift_card_code || ($record?->discount_order ?? 0) > 0))
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 3,
                ])
                ->schema([
                    // Grup Promo & Voucher
                    Forms\Components\Fieldset::make('Voucher & Promo')
                        ->columns(1)
                        ->visible(fn () => Feature::enabled('promotions'))
                        ->schema([
                            Forms\Components\TextInput::make('promotion_code')
                                ->label('Kode Promo / Voucher')
                                ->placeholder('Contoh: PROMO10')
                                ->prefixIcon('heroicon-o-tag')
                                ->maxLength(50)
                                ->helperText('Divalidasi saat order disimpan.')
                                ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                                ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state),

                            Forms\Components\TextInput::make('promotion_discount')
                                ->label('Potongan Promo')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false)
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    $subtotal = $get('subtotal_order') ?? 0;
                                    $manual = $get('discount_order') ?? 0;
                                    $giftCardAmount = $get('gift_card_amount') ?? 0;
                                    $set('total_order', max($subtotal - $manual - ($state ?? 0) - $giftCardAmount, 0));
                                })
                                ->helperText('Terisi otomatis setelah promo valid.'),
                        ]),

                    // Grup Gift Card
                    Forms\Components\Fieldset::make('Gift Card / Corporate')
                        ->columns(1)
                        ->visible(fn () => Feature::enabled('gift_cards'))
                        ->schema([
                            Forms\Components\TextInput::make('gift_card_code')
                                ->label('Kode Gift Card')
                                ->placeholder('Contoh: GC-XXXX')
                                ->prefixIcon('heroicon-o-gift')
                                ->helperText('Saldo akan dipotong setelah valid.')
                                ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                                ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                                ->disabled(fn (?Order $record) => $record?->exists ?? false),

                            Forms\Components\TextInput::make('gift_card_amount')
                                ->label('Nominal Digunakan')
                                ->prefix('Rp')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText('Maksimal sesuai sisa tagihan.')
                                ->reactive()
                                ->disabled(fn (?Order $record) => $record?->exists ?? false)
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    $subtotal = $get('subtotal_order') ?? 0;
                                    $manual = $get('discount_order') ?? 0;
                                    $promoDiscount = $get('promotion_discount') ?? 0;
                                    $set('total_order', max($subtotal - $manual - $promoDiscount - ($state ?? 0), 0));
                                }),
                        ]),

                    // Grup Kalkulasi & Total
                    Forms\Components\Fieldset::make('Ringkasan Pembayaran')
                        ->columns(1)
                        ->columnSpan(fn () => (! Feature::enabled('promotions') || ! Feature::enabled('gift_cards')) ? 2 : 1)
                        ->schema([
                            Forms\Components\TextInput::make('discount_order')
                                ->label('Diskon Manual Kasir')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    $subtotal = $get('subtotal_order') ?? 0;
                                    $promoDiscount = $get('promotion_discount') ?? 0;
                                    $giftCardAmount = $get('gift_card_amount') ?? 0;
                                    $set('total_order', max($subtotal - $state - $promoDiscount - $giftCardAmount, 0));
                                }),

                            Forms\Components\TextInput::make('subtotal_order')
                                ->label('Subtotal Produk')
                                ->prefix('Rp')
                                ->numeric()
                                ->disabled()
                                ->default(0)
                                ->helperText('Akumulasi harga produk dari keranjang.'),

                            Forms\Components\TextInput::make('total_order')
                                ->label('Total Akhir / Tagihan')
                                ->prefix('Rp')
                                ->numeric()
                                ->disabled()
                                ->default(0)
                                ->extraInputAttributes(['class' => '!text-base !font-extrabold !text-primary-600 dark:!text-primary-400'])
                                ->helperText('Total bayar setelah semua potongan.'),
                        ]),
                ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->poll('4s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label(__('No. Order'))
                ->sortable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label(__('Tipe Order'))
                    ->formatStateUsing(fn (string $state): string => __('orders.types.' . $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('table.table_number')
                    ->label(__('Meja'))
                    ->sortable()
                    ->visible(fn () => Feature::enabled('table_management')),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => Feature::enabled('loyalty')),

                Tables\Columns\TextColumn::make('total_order')
                    ->label(__('Total'))
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('promotion_code')
                    ->label('Kode Promo')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => Feature::enabled('promotions')),
                Tables\Columns\TextColumn::make('gift_card_code')
                    ->label('Gift Card')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => Feature::enabled('gift_cards')),
                Tables\Columns\TextColumn::make('gift_card_amount')
                    ->label('Nominal Gift Card')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => Feature::enabled('gift_cards')),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'primary' => 'open',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => __('orders.status.' . $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Waktu Order'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Optional filter status
            ])
            ->actions([
                Tables\Actions\Action::make('print_kitchen')
                    ->label('Tiket Dapur')
                    ->icon('heroicon-o-fire')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('orders.print.kitchen', ['order' => $record]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('void_order')
                    ->label('Batalkan (Void)')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => ! in_array((string) $record->status, [OrderStatus::Cancelled->value, OrderStatus::Completed->value]))
                    ->form([
                        Forms\Components\Select::make('reason')
                            ->label('Alasan Pembatalan')
                            ->options([
                                'Pelanggan Membatalkan' => 'Pelanggan Membatalkan',
                                'Salah Input Menu' => 'Salah Input Menu',
                                'Bahan Baku Habis' => 'Bahan Baku Habis',
                                'Pesanan Duplikat' => 'Pesanan Duplikat',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->default('Pelanggan Membatalkan'),
                        Forms\Components\TextInput::make('notes')
                            ->label('Keterangan Tambahan')
                            ->placeholder('Opsional: rincian alasan...'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            if ($record->stock_deducted) {
                                StockService::restoreIngredientsFromOrder($record);
                                $record->stock_deducted = false;
                            }

                            $record->status = OrderStatus::Cancelled->value;
                            $record->save();

                            $reasonText = $data['reason'] . (!empty($data['notes']) ? ' (' . $data['notes'] . ')' : '');
                            $record->logStatus(OrderStatus::Cancelled, 'Dibatalkan oleh kasir: ' . $reasonText);
                        });

                        Notification::make()
                            ->title("Pesanan #{$record->id} berhasil dibatalkan")
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pesanan (Void)')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pesanan ini? Stok bahan baku yang sudah dipotong akan dikembalikan otomatis.'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // ExportBulkAction::make() // tanpa job ,
                    ExportBulkAction::make()
                    ->exporter(OrderExporter::class),
                    BulkAction::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        $orders = $records;

                        $pdf = Pdf::loadView('exports.order-pdf', ['orders' => $orders]);

                        return response()->streamDownload(
                            fn () => print($pdf->stream()),
                            'laporan-order-'.now()->format('Ymd_His').'.pdf'
                        );
                    })
                    ->requiresConfirmation()
                    ->color('primary')
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // // Bisa future: pembayaran atau item
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
