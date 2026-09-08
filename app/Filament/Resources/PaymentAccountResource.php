<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentAccountResource\Pages;
use App\Models\PaymentAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentAccountResource extends Resource
{
    protected static ?string $model = PaymentAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Pembayaran';

    protected static ?string $navigationLabel = 'Rekening & E-Wallet';

    protected static ?string $modelLabel = 'Rekening / E-Wallet';

    protected static ?string $pluralModelLabel = 'Rekening Bank & E-Wallet';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['admin', 'owner', 'superadmin']);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rekening / Akun')
                    ->description('Konfigurasi nomor rekening bank atau akun e-wallet untuk menerima pembayaran non-tunai dari pelanggan.')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Metode')
                            ->options([
                                'bank_transfer' => '🏦 Transfer Bank (Rekening)',
                                'ewallet'       => '💳 E-Wallet (Dompet Digital)',
                            ])
                            ->default('bank_transfer')
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bank / E-Wallet')
                            ->placeholder('Contoh: BCA, Bank Mandiri, GoPay, DANA')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('provider_code')
                            ->label('Kode Provider')
                            ->placeholder('Contoh: bca, mandiri, bri, gopay, dana')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Kode unik huruf kecil tanpa spasi (contoh: bca, gopay).'),

                        Forms\Components\TextInput::make('account_number')
                            ->label(fn (Forms\Get $get) => $get('type') === 'ewallet' ? 'Nomor Akun / No. HP E-Wallet' : 'Nomor Rekening Bank')
                            ->placeholder('Contoh: 1234567890 atau 08123456789')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Atas Nama (A/N Pemilik)')
                            ->placeholder('Contoh: Kafe KDAI')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Semakin kecil angka urutan, semakin awal ditampilkan di daftar.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Aktifkan agar metode ini muncul di kasir dan halaman pembayaran pelanggan.')
                            ->default(true),

                        Forms\Components\FileUpload::make('qr_image')
                            ->label('Upload Gambar QR Code Statis (Opsional)')
                            ->disk('public')
                            ->directory('payment-qrs')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('Opsional: Unggah gambar QR code statis rekening/e-wallet Anda.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('instructions')
                            ->label('Petunjuk Pembayaran')
                            ->placeholder('Contoh: Mohon sertakan nomor pesanan di berita transfer dan simpan bukti bayar.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'info'    => 'bank_transfer',
                        'success' => 'ewallet',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'bank_transfer' => '🏦 Transfer Bank',
                        'ewallet'       => '💳 E-Wallet',
                        default         => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Bank / E-Wallet')
                    ->description(fn (PaymentAccount $record) => "Kode: {$record->provider_code}")
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('Nomor Rekening / No. HP')
                    ->fontFamily(FontFamily::Mono)
                    ->copyable()
                    ->copyMessage('Nomor rekening disalin ke clipboard')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Atas Nama (A/N)')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('qr_image')
                    ->label('QR')
                    ->disk('public')
                    ->square()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'bank_transfer' => '🏦 Transfer Bank',
                        'ewallet'       => '💳 E-Wallet',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Keaktifan')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaymentAccounts::route('/'),
            'create' => Pages\CreatePaymentAccount::route('/create'),
            'edit'   => Pages\EditPaymentAccount::route('/{record}/edit'),
        ];
    }
}
