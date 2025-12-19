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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Orderan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')
                ->label('Customer')
                ->relationship('customer', 'name')
                ->searchable()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->nullable(),
                    Forms\Components\TextInput::make('phone')->tel()->nullable(),
                ])
                ->helperText('Opsional, bisa pilih customer untuk loyalty.')
                ->visible(fn () => Feature::enabled('loyalty')),
            Forms\Components\Select::make('table_id')
                ->label('Meja (Opsional)')
                ->options(function (callable $get) {
                    $current = $get('table_id');

                    return CafeTable::query()
                        ->where(function ($query) use ($current) {
                            $query->whereIn('status', ['available', 'reserved']);

                            if ($current) {
                                $query->orWhere('id', $current);
                            }
                        })
                        ->orderBy('table_number')
                        ->pluck('table_number', 'id');
                })
                ->searchable()
                ->preload()
                ->helperText('Menampilkan meja kosong / reserved. Meja terisi tidak akan muncul kecuali sedang diedit.')
                ->nullable()
                ->visible(fn () => Feature::enabled('table_management')),

            Forms\Components\Select::make('order_type')
                ->label('Tipe Order')
                ->options([
                    'dine_in' => 'Dine In',
                    'take_away' => 'Take Away',
                    'delivery' => 'Delivery',
                ])
                ->required(),

            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Customer')
                ->nullable()
                ->maxLength(100),

            Forms\Components\TextInput::make('subtotal_order')
                ->label('Subtotal Order')
                ->numeric()
                ->disabled()
                ->default(0),

            Forms\Components\TextInput::make('discount_order')
                ->label('Diskon Manual')
                ->numeric()
                ->default(0)
                ->reactive()
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    $subtotal = $get('subtotal_order') ?? 0;
                    $promoDiscount = $get('promotion_discount') ?? 0;
                    $giftCardAmount = $get('gift_card_amount') ?? 0;
                    $set('total_order', max($subtotal - $state - $promoDiscount - $giftCardAmount, 0));
                }),

            Forms\Components\TextInput::make('promotion_code')
                ->label('Kode Promo / Voucher')
                ->maxLength(50)
                ->helperText('Masukkan kode promo jika ada. Validasi akan dilakukan saat order disimpan.')
                ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                ->visible(fn () => Feature::enabled('promotions')),

            Forms\Components\TextInput::make('promotion_discount')
                ->label('Diskon Promo')
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
                ->helperText('Terisi otomatis setelah promo valid.')
                ->visible(fn () => Feature::enabled('promotions')),

            Forms\Components\TextInput::make('gift_card_code')
                ->label('Gift Card / Corporate Code')
                ->helperText('Masukkan kode gift card untuk pembayaran. Akan divalidasi saat disimpan.')
                ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                ->disabled(fn (?Order $record) => $record?->exists ?? false)
                ->visible(fn () => Feature::enabled('gift_cards')),

            Forms\Components\TextInput::make('gift_card_amount')
                ->label('Nominal Gift Card')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText('Nilai yang dipakai tidak boleh melebihi tagihan sisa. Tidak dapat diubah setelah order dibuat.')
                ->reactive()
                ->disabled(fn (?Order $record) => $record?->exists ?? false)
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    $subtotal = $get('subtotal_order') ?? 0;
                    $manual = $get('discount_order') ?? 0;
                    $promoDiscount = $get('promotion_discount') ?? 0;
                    $set('total_order', max($subtotal - $manual - $promoDiscount - ($state ?? 0), 0));
                })
                ->visible(fn () => Feature::enabled('gift_cards')),

            Forms\Components\TextInput::make('total_order')
                ->label('Total Bayar')
                ->numeric()
                ->disabled()
                ->default(0),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('No. Order')
                ->sortable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Tipe Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('table.table_number')
                    ->label('Meja')
                    ->sortable()
                    ->visible(fn () => Feature::enabled('table_management')),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => Feature::enabled('loyalty')),

                Tables\Columns\TextColumn::make('total_order')
                    ->label('Total')
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
                    ->label('Status')
                    ->colors([
                        'primary' => 'open',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Order')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Optional filter status
            ])
            ->actions([
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
