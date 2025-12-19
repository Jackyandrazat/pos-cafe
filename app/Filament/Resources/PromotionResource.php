<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Promotion;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Support\Feature;
use App\Filament\Resources\PromotionResource\Pages;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Promo & Voucher';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Promo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Promo')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Voucher')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                            ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                            ->helperText('Akan otomatis diubah menjadi huruf kapital.'),
                        Forms\Components\Select::make('type')
                            ->label('Tipe Diskon')
                            ->options([
                                'fixed' => 'Nominal',
                                'percentage' => 'Persentase',
                            ])
                            ->default('fixed')
                            ->required(),
                        Forms\Components\TextInput::make('discount_value')
                            ->label('Nilai Diskon')
                            ->numeric()
                            ->required()
                            ->helperText('Jika persentase gunakan angka 0-100.'),
                        Forms\Components\TextInput::make('max_discount')
                            ->label('Maksimal Diskon')
                            ->numeric()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                            ->helperText('Opsional, batas diskon saat tipe persentase.'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Kondisi Penggunaan')
                    ->schema([
                        Forms\Components\TextInput::make('min_subtotal')
                            ->label('Minimal Subtotal')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Batas Global')
                            ->numeric()
                            ->minValue(0)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                            ->helperText('Dikosongkan jika tidak dibatasi.'),
                        Forms\Components\TextInput::make('usage_limit_per_user')
                            ->label('Batas per Pengguna')
                            ->numeric()
                            ->minValue(0)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Mulai Berlaku')
                            ->seconds(false)
                            ->formatStateUsing(fn ($state) =>
                                $state
                                    ? Carbon::parse($state)->timezone('Asia/Jakarta')
                                    : null
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                $state
                                    ? Carbon::parse($state, 'Asia/Jakarta')->utc()
                                    : null
                            ),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Berakhir')
                            ->seconds(false)
                            ->minDate(fn (Get $get) => $get('starts_at'))
                            ->formatStateUsing(fn ($state) =>
                                $state
                                    ? Carbon::parse($state)->timezone('Asia/Jakarta')
                                    : null
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                $state
                                    ? Carbon::parse($state, 'Asia/Jakarta')->utc()
                                    : null
                            ),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(3),
                Forms\Components\Section::make('Jadwal Dinamis (Opsional)')
                    ->schema([
                        Forms\Components\CheckboxList::make('schedule_days')
                            ->label('Hari Berlaku')
                            ->options([
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                            ])
                            ->columns(2)
                            ->helperText('Kosongkan jika promo berlaku setiap hari.'),
                        Forms\Components\TimePicker::make('schedule_start_time')
                            ->label('Mulai (Jam)')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('schedule_end_time')
                            ->label('Selesai (Jam)')
                            ->seconds(false)
                            ->helperText('Jika jam akhir lebih kecil dari awal → dianggap melewati tengah malam.'),
                    ])->columns(3),
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('promotions');
    }

    public static function canViewAny(): bool
    {
        return Feature::enabled('promotions');
    }

    public static function canCreate(): bool
    {
        return Feature::enabled('promotions');
    }

    public static function canEdit($record): bool
    {
        return Feature::enabled('promotions');
    }

    public static function canDelete($record): bool
    {
        return Feature::enabled('promotions');
    }

    public static function canDeleteAny(): bool
    {
        return Feature::enabled('promotions');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(function (Promotion $record) {
                        return $record->type === 'percentage'
                            ? $record->discount_value . '%'
                            : 'Rp ' . number_format($record->discount_value, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Kuota')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '∞'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->formatStateUsing(fn ($state) =>
                        $state
                            ? Carbon::parse($state)->timezone('Asia/Jakarta')->format('d M Y H:i')
                            : '-'
                    ),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->formatStateUsing(fn ($state) =>
                        $state
                            ? Carbon::parse($state)->timezone('Asia/Jakarta')->format('d M Y H:i')
                            : '-'
                    ),

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
