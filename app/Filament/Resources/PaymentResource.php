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
    protected static ?int $navigationSort     = 1;

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
        $dbEwallets = \App\Models\PaymentAccount::active()->ewallets()->get();
        $ewallets = $dbEwallets->isNotEmpty()
            ? $dbEwallets->mapWithKeys(fn ($acc) => [$acc->provider_code => "{$acc->name} - {$acc->account_number} (a.n {$acc->account_name})"])
            : collect(config('payment.ewallets', []))->mapWithKeys(fn ($v, $k) => [$k => $v['label']]);

        $dbVas = \App\Models\PaymentAccount::active()->bankTransfers()->get();
        $vas = $dbVas->isNotEmpty()
            ? $dbVas->mapWithKeys(fn ($acc) => [$acc->provider_code => "{$acc->name} - {$acc->account_number} (a.n {$acc->account_name})"])
            : collect(config('payment.virtual_accounts', []))->mapWithKeys(fn ($v, $k) => [$k => $v['label']]);

        return $form->schema([
            Forms\Components\Section::make('Informasi Order')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Pilih Order yang Akan Dibayar')
                        ->getSearchResultsUsing(function (string $search) {
                            return Order::with(['table', 'customer'])
                                ->whereNotIn('status', ['completed', 'cancelled'])
                                ->where(function ($q) use ($search) {
                                    $q->where('id', 'like', "%{$search}%")
                                        ->orWhere('customer_name', 'like', "%{$search}%")
                                        ->orWhereHas('table', fn ($t) => $t->where('table_number', 'like', "%{$search}%"));
                                })
                                ->limit(30)
                                ->get()
                                ->mapWithKeys(function ($order) {
                                    $tableText = $order->table ? "Meja {$order->table->table_number}" : ucfirst($order->order_type ?? 'Takeaway');
                                    $custText  = $order->customer_name ?: ($order->customer?->name ?: 'Umum');
                                    $totalText = 'Rp ' . number_format($order->total_order, 0, ',', '.');

                                    return [$order->id => "Order #{$order->id} — {$tableText} ({$custText}) • {$totalText}"];
                                })
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $order = Order::with('table')->find($value);
                            if (! $order) {
                                return "Order #{$value}";
                            }
                            $tableText = $order->table ? "Meja {$order->table->table_number}" : ucfirst($order->order_type ?? 'Takeaway');
                            $custText  = $order->customer_name ?: 'Umum';
                            $totalText = 'Rp ' . number_format($order->total_order, 0, ',', '.');

                            return "Order #{$order->id} — {$tableText} ({$custText}) • {$totalText}";
                        })
                        ->searchable()
                        ->options(function () {
                            return Order::with(['table', 'customer'])
                                ->whereNotIn('status', ['completed', 'cancelled'])
                                ->latest()
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(function ($order) {
                                    $tableText = $order->table ? "Meja {$order->table->table_number}" : ucfirst($order->order_type ?? 'Takeaway');
                                    $custText  = $order->customer_name ?: ($order->customer?->name ?: 'Umum');
                                    $totalText = 'Rp ' . number_format($order->total_order, 0, ',', '.');

                                    return [$order->id => "Order #{$order->id} — {$tableText} ({$custText}) • {$totalText}"];
                                })
                                ->toArray();
                        })
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
                        ->default(fn () => request()->query('order_id') ? (int) request()->query('order_id') : null)
                        ->required(),

                    Forms\Components\Placeholder::make('order_summary_card')
                        ->label('Ringkasan Pesanan')
                        ->content(function (Forms\Get $get) {
                            $orderId = $get('order_id');
                            if (! $orderId) {
                                return new \Illuminate\Support\HtmlString('
                                    <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 0.85rem; padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.82rem;">
                                        <span>👈 Pilih order di atas untuk melihat rincian tagihan & daftar menu pesanan.</span>
                                    </div>
                                ');
                            }

                            $order = Order::with(['table', 'order_items.product', 'promotion'])->find($orderId);
                            if (! $order) {
                                return '-';
                            }

                            $tableLabel   = $order->table ? "🪑 Meja {$order->table->table_number}" : '🛍️ Takeaway / Bungkus';
                            $customerName = htmlspecialchars($order->customer_name ?: 'Pelanggan Umum', ENT_QUOTES, 'UTF-8');
                            $itemCount    = $order->order_items->sum('qty');

                            $itemsHtml = '';
                            foreach ($order->order_items->take(6) as $item) {
                                $prodName = htmlspecialchars($item->product?->name ?? 'Item', ENT_QUOTES, 'UTF-8');
                                $qty      = $item->qty;
                                $subtotal = 'Rp ' . number_format($item->subtotal ?: ($item->price * $qty), 0, ',', '.');
                                $itemsHtml .= "
                                    <div style='display: flex; justify-content: space-between; align-items: center; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem;'>
                                        <div><span style='font-weight: 700; color: #0284c7;'>{$qty}x</span> <span style='color: #1e293b; font-weight: 600;'>{$prodName}</span></div>
                                        <div style='color: #475569; font-weight: 700; font-family: monospace;'>{$subtotal}</div>
                                    </div>";
                            }
                            if ($order->order_items->count() > 6) {
                                $moreCount = $order->order_items->count() - 6;
                                $itemsHtml .= "<div style='font-size: 0.75rem; color: #64748b; font-style: italic; margin-top: 0.25rem;'>+ {$moreCount} item lainnya...</div>";
                            }

                            $discountHtml = '';
                            if ($order->discount_order > 0 || $order->promotion_discount > 0) {
                                $discVal = 'Rp ' . number_format(($order->discount_order + $order->promotion_discount), 0, ',', '.');
                                $discountHtml = "
                                    <div style='display: flex; justify-content: space-between; font-size: 0.82rem; color: #dc2626; font-weight: 700;'>
                                        <span>Diskon / Promo:</span>
                                        <span>- {$discVal}</span>
                                    </div>";
                            }

                            $formattedTotal = 'Rp ' . number_format($order->total_order, 0, ',', '.');

                            return new \Illuminate\Support\HtmlString("
                                <div style='background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 1rem; padding: 0.85rem 1.15rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);'>
                                    <div style='display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 0.5rem;'>
                                        <div style='display: flex; align-items: center; gap: 0.5rem;'>
                                            <span style='background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 9999px;'>
                                                {$tableLabel}
                                            </span>
                                            <span style='font-weight: 800; color: #1e293b; font-size: 0.85rem;'>
                                                {$customerName}
                                            </span>
                                        </div>
                                        <span style='font-size: 0.72rem; font-weight: 700; color: #64748b;'>
                                            {$itemCount} Item
                                        </span>
                                    </div>

                                    <div style='margin-bottom: 0.6rem;'>
                                        {$itemsHtml}
                                    </div>

                                    <div style='border-top: 1.5px dashed #cbd5e1; padding-top: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;'>
                                        {$discountHtml}
                                        <div style='display: flex; justify-content: space-between; align-items: center;'>
                                            <span style='font-weight: 800; font-size: 0.88rem; color: #0f172a;'>Total Tagihan:</span>
                                            <span style='font-weight: 900; font-size: 1.2rem; color: #059669; font-family: monospace; letter-spacing: -0.02em;'>
                                                {$formattedTotal}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            ");
                        })
                        ->columnSpanFull(),
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

                    Forms\Components\Placeholder::make('channel_account_info')
                        ->label('Informasi Rekening / Akun Tujuan')
                        ->content(function (Forms\Get $get) {
                            $method  = $get('payment_method');
                            $channel = $get('payment_channel');
                            if (! in_array($method, ['ewallet', 'transfer']) || ! $channel) {
                                return null;
                            }

                            $account = \App\Models\PaymentAccount::where('provider_code', $channel)->first();
                            if (! $account) {
                                return null;
                            }

                            $icon      = $method === 'transfer' ? '🏦' : '📱';
                            $accName   = htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8');
                            $accNumber = htmlspecialchars($account->account_number, ENT_QUOTES, 'UTF-8');
                            $ownerName = htmlspecialchars($account->account_name, ENT_QUOTES, 'UTF-8');

                            return new \Illuminate\Support\HtmlString("
                                <div style='background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 0.85rem; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between;'>
                                    <div style='display: flex; align-items: center; gap: 0.75rem;'>
                                        <span style='font-size: 1.6rem;'>{$icon}</span>
                                        <div>
                                            <div style='font-size: 0.75rem; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em;'>
                                                {$accName}
                                            </div>
                                            <div style='font-size: 1.15rem; font-weight: 900; color: #1e3a8a; font-family: monospace; letter-spacing: 0.05em;'>
                                                {$accNumber}
                                            </div>
                                            <div style='font-size: 0.78rem; color: #3b82f6;'>
                                                a.n <strong>{$ownerName}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div style='text-align: right;'>
                                        <span style='font-size: 0.72rem; background: #dbeafe; color: #1d4ed8; padding: 0.25rem 0.6rem; border-radius: 0.45rem; font-weight: 800;'>
                                            📢 Beritahu Tamu
                                        </span>
                                    </div>
                                </div>
                            ");
                        })
                        ->visible(fn (Forms\Get $get) => in_array($get('payment_method'), ['ewallet', 'transfer']) && ! empty($get('payment_channel')))
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('amount_paid')
                        ->label('Jumlah Bayar')
                        ->prefix('Rp')
                        ->numeric()
                        ->live()
                        ->rules([
                            fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('payment_method') === 'cash') {
                                    $orderId = $get('order_id');
                                    if ($orderId && $order = Order::find($orderId)) {
                                        if ((float) $value < (float) $order->total_order) {
                                            $shortage = (float) $order->total_order - (float) $value;
                                            $fail('⚠️ Uang tunai kurang Rp ' . number_format($shortage, 0, ',', '.') . ' dari total tagihan (Rp ' . number_format($order->total_order, 0, ',', '.') . ').');
                                        }
                                    }
                                }
                            },
                        ])
                        ->helperText(function (Forms\Get $get) {
                            $orderId = $get('order_id');
                            if (! $orderId) {
                                return 'Pilih order terlebih dahulu.';
                            }
                            $order = Order::find($orderId);
                            if (! $order) {
                                return null;
                            }

                            $paid  = (float) ($get('amount_paid') ?? 0);
                            $total = (float) $order->total_order;
                            if ($get('payment_method') === 'cash' && $paid > 0 && $paid < $total) {
                                return '⚠️ Uang kurang Rp ' . number_format($total - $paid, 0, ',', '.') . '. Masukkan nominal yang cukup.';
                            }

                            return 'Total tagihan: Rp ' . number_format($total, 0, ',', '.') . '. Gunakan tombol cepat di bawah untuk kemudahan kasir.';
                        })
                        ->required(),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('uang_pas')
                            ->label('💵 Uang Pas')
                            ->color('success')
                            ->button()
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $orderId = $get('order_id');
                                if ($orderId && $order = Order::find($orderId)) {
                                    $set('amount_paid', (float) $order->total_order);
                                }
                            }),

                        Forms\Components\Actions\Action::make('round_50k')
                            ->label('🎯 Bulat 50k')
                            ->color('warning')
                            ->button()
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $orderId = $get('order_id');
                                if ($orderId && $order = Order::find($orderId)) {
                                    $total   = (float) $order->total_order;
                                    $rounded = ceil($total / 50000) * 50000;
                                    $set('amount_paid', $rounded > 0 ? $rounded : 50000);
                                }
                            }),

                        Forms\Components\Actions\Action::make('round_100k')
                            ->label('🎯 Bulat 100k')
                            ->color('warning')
                            ->button()
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $orderId = $get('order_id');
                                if ($orderId && $order = Order::find($orderId)) {
                                    $total   = (float) $order->total_order;
                                    $rounded = ceil($total / 100000) * 100000;
                                    $set('amount_paid', $rounded > 0 ? $rounded : 100000);
                                }
                            }),

                        Forms\Components\Actions\Action::make('cash_10k')
                            ->label('Rp 10.000')
                            ->color('gray')
                            ->button()
                            ->action(fn (Forms\Set $set) => $set('amount_paid', 10000)),

                        Forms\Components\Actions\Action::make('cash_20k')
                            ->label('Rp 20.000')
                            ->color('gray')
                            ->button()
                            ->action(fn (Forms\Set $set) => $set('amount_paid', 20000)),

                        Forms\Components\Actions\Action::make('cash_50k')
                            ->label('Rp 50.000')
                            ->color('gray')
                            ->button()
                            ->action(fn (Forms\Set $set) => $set('amount_paid', 50000)),

                        Forms\Components\Actions\Action::make('cash_100k')
                            ->label('Rp 100.000')
                            ->color('gray')
                            ->button()
                            ->action(fn (Forms\Set $set) => $set('amount_paid', 100000)),

                        Forms\Components\Actions\Action::make('plus_10k')
                            ->label('+10k')
                            ->color('info')
                            ->button()
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $curr = (float) ($get('amount_paid') ?? 0);
                                $set('amount_paid', $curr + 10000);
                            }),

                        Forms\Components\Actions\Action::make('plus_50k')
                            ->label('+50k')
                            ->color('info')
                            ->button()
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $curr = (float) ($get('amount_paid') ?? 0);
                                $set('amount_paid', $curr + 50000);
                            }),

                        Forms\Components\Actions\Action::make('reset_cash')
                            ->label('↺ Kosongkan')
                            ->color('danger')
                            ->button()
                            ->action(fn (Forms\Set $set) => $set('amount_paid', null)),
                    ])
                        ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('kembalian_calculator')
                        ->label('Perhitungan Kembalian')
                        ->content(function (Forms\Get $get) {
                            $orderId = $get('order_id');
                            $paid = (float) ($get('amount_paid') ?? 0);
                            if (! $orderId || $paid <= 0) {
                                return new \Illuminate\Support\HtmlString('<span class="text-sm text-gray-400 italic">Pilih order dan masukkan nominal uang diterima.</span>');
                            }

                            $order = Order::find($orderId);
                            if (! $order) {
                                return '-';
                            }

                            $total = (float) $order->total_order;
                            $diff = $paid - $total;

                            if ($diff < 0) {
                                return new \Illuminate\Support\HtmlString('
                                    <div style="background-color: #fef2f2; color: #b91c1c; border: 1.5px solid #fecaca; border-radius: 0.75rem; padding: 0.5rem 0.85rem; display: inline-flex; align-items: center; gap: 0.6rem;">
                                        <span style="font-weight: 800; font-size: 0.85rem;">⚠️ Uang Kurang:</span>
                                        <span style="font-weight: 900; font-family: monospace; font-size: 1.05rem;">Rp ' . number_format(abs($diff), 0, ',', '.') . '</span>
                                    </div>
                                ');
                            }

                            return new \Illuminate\Support\HtmlString('
                                <div style="background-color: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; border-radius: 0.75rem; padding: 0.5rem 0.85rem; display: inline-flex; align-items: center; gap: 0.6rem;">
                                    <span style="font-weight: 800; font-size: 0.85rem;">💵 Kembalian:</span>
                                    <span style="font-weight: 900; font-family: monospace; font-size: 1.15rem; letter-spacing: -0.01em;">Rp ' . number_format($diff, 0, ',', '.') . '</span>
                                </div>
                            ');
                        })
                        ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                        ->columnSpanFull(),
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
                    ->label(__('Order #'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Metode'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash'     => '💵 Cash',
                        'qris'     => '📱 QRIS',
                        'ewallet'  => '💳 E-Wallet',
                        'transfer' => '🏦 Transfer',
                        default    => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_channel')
                    ->label(__('Channel'))
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'warning' => PaymentStatus::Pending->value,
                        'success' => PaymentStatus::Captured->value,
                        'danger'  => PaymentStatus::Failed->value,
                        'gray'    => [PaymentStatus::Expired->value, PaymentStatus::Refunded->value],
                    ])
                    ->formatStateUsing(fn ($state) => PaymentStatus::tryFrom($state)?->label() ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label(__('Jumlah'))
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_return')
                    ->label(__('Kembalian'))
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
                            Notification::make()
                                ->title('Pembayaran dikonfirmasi!')
                                ->body('Pembayaran berhasil diverifikasi. Silakan cetak struk untuk pelanggan.')
                                ->success()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('print')
                                        ->label('🖨️ Cetak Struk')
                                        ->button()
                                        ->url(route('payments.print', ['payment' => $record]), shouldOpenInNewTab: true),
                                ])
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('print_receipt')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->visible(fn (Payment $record): bool => $record->isCaptured() && $record->order !== null)
                    ->url(fn (Payment $record): string => route('payments.print', ['payment' => $record]))
                    ->openUrlInNewTab(),

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
        $order = $payment->order?->loadMissing('items.product', 'order_items.product', 'table');
        if (! $order) {
            return '#';
        }

        $customerName = $order->customer_name ?? 'Pelanggan';
        $items        = ($order->items && $order->items->isNotEmpty()) ? $order->items : ($order->order_items ?? collect());
        $cafeName     = \App\Helpers\SettingHelper::get('receipt_cafe_name', \App\Helpers\SettingHelper::get('cafe_name', config('app.name', 'PSAN')));

        $lines   = [];
        $lines[] = "*Struk Pembelian Cafe* - {$cafeName}";
        $lines[] = "Order #{$order->id} | Ref: #PAY-{$payment->id}";
        $lines[] = "Customer: {$customerName}";
        if ($order->table?->table_number) {
            $lines[] = "Meja: {$order->table->table_number}";
        }
        $lines[] = '---------------------------';

        foreach ($items as $item) {
            $productName = $item->product?->name ?? 'Produk';
            $qty         = $item->qty ?? 1;
            $price       = number_format($item->price, 0, ',', '.');
            $lines[]     = "{$qty}x {$productName} @ Rp{$price}";
        }

        $lines[] = '---------------------------';
        $lines[] = '*Total Bayar: Rp' . number_format($payment->amount_paid, 0, ',', '.') . '*';
        if (($payment->change_return ?? 0) > 0) {
            $lines[] = 'Kembalian: Rp' . number_format($payment->change_return, 0, ',', '.');
        }
        $lines[] = 'Metode: ' . strtoupper($payment->payment_method);

        $wifiSsid = \App\Helpers\SettingHelper::get('receipt_wifi_ssid');
        $wifiPass = \App\Helpers\SettingHelper::get('receipt_wifi_password');
        if ($wifiSsid) {
            $lines[] = "WiFi: {$wifiSsid} (Pass: {$wifiPass})";
        }

        $footer = \App\Helpers\SettingHelper::get('receipt_footer_text', 'Terima kasih telah berkunjung!');
        $lines[] = '';
        $lines[] = $footer;

        $text = implode('%0A', $lines);

        $rawPhone = $order->customer_phone ?? $order->customer?->phone ?? null;
        if ($rawPhone) {
            $phone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($phone, '08')) {
                $phone = '62' . substr($phone, 1);
            }
            return "https://api.whatsapp.com/send?phone={$phone}&text={$text}";
        }

        return "https://api.whatsapp.com/send?text={$text}";
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
