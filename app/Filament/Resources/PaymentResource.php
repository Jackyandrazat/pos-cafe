<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PaymentResource\RelationManagers;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\URL;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    // protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                ->label('Order')
                ->getSearchResultsUsing(function (string $search) {
                    return \App\Models\Order::where('status', '!=', 'completed')
                        ->where('id', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('id', 'id')
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value): string {
                    $order = \App\Models\Order::find($value);
                    return $order ? "Order #{$order->id}" : (string)$value;
                })
                ->searchable()
                ->options(Order::where('status', '!=', 'completed')->pluck('id', 'id')) // filter di dropdown
                ->required(),


                Forms\Components\Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('amount_paid')
                    ->label('Jumlah Bayar')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('change_return')
                    ->label('Kembalian')
                    ->numeric()
                    ->disabled()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                ->label('Order ID')
                ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Jumlah Bayar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_return')
                    ->label('Kembalian')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('kirim_wa')
                    ->label('Kirim WA')
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => $record->order?->status === 'completed')
                    ->url(fn ($record) => self::generateWhatsappLink($record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function generateWhatsappLink($payment): string
    {
        $order = $payment->order;
        $customerName = $order->customer_name ?? 'Pelanggan';

        // Pastikan $items tidak null
        $items = $order->orderItems ?? collect();

        $lines = [];
        $lines[] = "*Struk Pembelian Cafe*";
        $lines[] = "Customer: {$customerName}";
        $lines[] = "---------------------------";

        foreach ($items as $item) {
            $productName = $item->product?->name ?? 'Produk';
            $qty = $item->qty ?? 1;
            $price = number_format($item->price, 0, ',', '.');
            $lines[] = "{$qty}x {$productName} @ Rp{$price}";
        }

        $lines[] = "---------------------------";
        $lines[] = "*Total Bayar: Rp" . number_format($payment->amount_paid, 0, ',', '.') . "*";
        $lines[] = "";
        $lines[] = "Terima kasih telah berkunjung!";

        $text = implode('%0A', $lines);

        $nomor = '+6281268120488'; // Ganti sesuai nomor tujuan

        return "https://wa.me/{$nomor}?text={$text}";
    }



    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
