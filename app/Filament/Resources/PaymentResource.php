<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PaymentsExporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\PaymentResource\Pages;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?int $navigationSort     = 2;

    public static function form(Form $form): Form
    {
        $ewallets = collect(config('payment.ewallets', []))->mapWithKeys(fn ($v, $k) => [$k => $v['label']]);
        $vas      = collect(config('payment.virtual_accounts', []))->mapWithKeys(fn ($v, $k) => [$k => $v['label']]);

        return $form->schema([
            Forms\Components\Section::make('Informasi Order')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Order')
                        ->getSearchResultsUsing(function (string $search) {
                            return Order::where('status', '!=', 'completed')
                                ->where('id', 'like', "%{$search}%")
                                ->limit(50)
                                ->pluck('id', 'id')
                                ->toArray();
                        })
                        ->getOptionLabelUsing(fn ($value) => "Order #{$value}")
                        ->searchable()
                        ->options(Order::whereNotIn('status', ['completed', 'cancelled'])->pluck('id', 'id'))
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                $order = Order::find($state);
                                if ($order) {
                                    $set('amount_paid', $order->total_order);
                                }
                            } else {
                                $set('amount_paid', null);
                            }
                        })
                        ->required(),
                ]),

            Forms\Components\Section::make('Metode Pembayaran')
                ->schema([
                    Forms\Components\Select::make('payment_method')
                        ->label('Metode')
                        ->options([
                            'cash'     => '💵 Cash',
                            'qris'     => '📱 QRIS',
                            'ewallet'  => '💳 E-Wallet',
                            'transfer' => '🏦 Transfer Bank / VA',
                        ])
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('payment_channel')
                        ->label('Channel')
                        ->options(fn (Forms\Get $get) => match ($get('payment_method')) {
                            'ewallet'  => $ewallets->toArray(),
                            'transfer' => $vas->toArray(),
                            default    => [],
                        })
                        ->hidden(fn (Forms\Get $get) => ! in_array($get('payment_method'), ['ewallet', 'transfer']))
                        ->nullable()
                        ->helperText('Pilih e-wallet atau bank tujuan.'),

                    Forms\Components\TextInput::make('amount_paid')
                        ->label('Jumlah Bayar')
                        ->prefix('Rp')
                        ->numeric()
                        ->live()
                        ->helperText(fn (Forms\Get $get) => $get('order_id')
                            ? 'Otomatis diisi dari total order. Bisa diubah jika perlu.'
                            : 'Pilih order terlebih dahulu.')
                        ->required(),
                ]),

            // Field teknis — hanya tampil di mode Edit (admin)
            Forms\Components\Section::make('Detail Teknis')
                ->schema([
                    Forms\Components\TextInput::make('provider')
                        ->label('Provider')
                        ->disabled()
                        ->dehydrated()
                        ->default('manual'),

                    Forms\Components\TextInput::make('external_reference')
                        ->label('Referensi Eksternal')
                        ->disabled()
                        ->dehydrated()
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                        ->default(PaymentStatus::Captured->value)
                        ->disabled()
                        ->dehydrated(),
                ])
                ->collapsed()
                ->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash'     => '💵 Cash',
                        'qris'     => '📱 QRIS',
                        'ewallet'  => '💳 E-Wallet',
                        'transfer' => '🏦 Transfer',
                        default    => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_channel')
                    ->label('Channel')
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => PaymentStatus::Pending->value,
                        'success' => PaymentStatus::Captured->value,
                        'danger'  => PaymentStatus::Failed->value,
                        'gray'    => [PaymentStatus::Expired->value, PaymentStatus::Refunded->value],
                    ])
                    ->formatStateUsing(fn ($state) => PaymentStatus::tryFrom($state)?->label() ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_return')
                    ->label('Kembalian')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('confirmedBy.name')
                    ->label('Dikonfirmasi Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options([
                        'cash'     => 'Cash',
                        'qris'     => 'QRIS',
                        'ewallet'  => 'E-Wallet',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                // Tombol konfirmasi pembayaran — tampil hanya untuk payment pending manual
                Action::make('confirm')
                    ->label('✅ Konfirmasi')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Payment $record) => $record->needsConfirmation())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription(fn (Payment $record) => "Konfirmasi bahwa pembayaran sebesar Rp " . number_format($record->amount_paid, 0, ',', '.') . " via " . strtoupper($record->payment_method) . " telah diterima?")
                    ->action(function (Payment $record) {
                        try {
                            app(\App\Services\Payments\PaymentService::class)->confirm($record, auth()->user());
                            Notification::make()->title('Pembayaran dikonfirmasi!')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('lihat_instruksi')
                    ->label('Instruksi')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn (Payment $record) => in_array($record->payment_method, ['qris', 'ewallet', 'transfer']) && filled($record->meta))
                    ->modalHeading('Instruksi Pembayaran')
                    ->modalContent(fn (Payment $record) => view('filament.components.payment-instructions', ['payment' => $record]))
                    ->modalWidth('md'),

                Action::make('kirim_wa')
                    ->label('Kirim WA')
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Payment $record) => $record->isCaptured() && $record->order?->status === 'completed')
                    ->url(fn (Payment $record) => self::generateWhatsappLink($record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(PaymentsExporter::class),
                ]),
            ]);
    }

    public static function generateWhatsappLink(Payment $payment): string
    {
        $order = $payment->order?->loadMissing('items.product');
        if (! $order) {
            return '#';
        }

        $customerName = $order->customer_name ?? 'Pelanggan';
        $items        = $order->items ?? collect();

        $lines   = [];
        $lines[] = '*Struk Pembelian Cafe*';
        $lines[] = "Customer: {$customerName}";
        $lines[] = '---------------------------';

        foreach ($items as $item) {
            $productName = $item->product?->name ?? 'Produk';
            $qty         = $item->qty ?? 1;
            $price       = number_format($item->price, 0, ',', '.');
            $lines[]     = "{$qty}x {$productName} @ Rp{$price}";
        }

        $lines[] = '---------------------------';
        $lines[] = '*Total Bayar: Rp' . number_format($payment->amount_paid, 0, ',', '.') . '*';
        $lines[] = '';
        $lines[] = 'Terima kasih telah berkunjung!';

        $text  = implode('%0A', $lines);
        $nomor = '+6281268120488';

        return "https://wa.me/{$nomor}?text={$text}";
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
