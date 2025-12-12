<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
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
            Forms\Components\Select::make('table_id')
                ->label('Meja (Opsional)')
                ->relationship('table', 'table_number')
                ->searchable()
                ->nullable(),

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
                ->label('Diskon Order')
                ->numeric()
                ->default(0)
                ->reactive()
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    $subtotal = $get('subtotal_order') ?? 0;
                    $set('total_order', max($subtotal - $state, 0));
                }),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_order')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

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
