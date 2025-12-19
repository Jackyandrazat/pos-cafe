<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftCardResource\Pages;
use App\Filament\Resources\GiftCardResource\RelationManagers\TransactionsRelationManager;
use App\Models\GiftCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GiftCardResource extends Resource
{
    protected static ?string $model = GiftCard::class;

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Gift Card & Corporate';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Gift Card')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state)
                        ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : $state),
                    Forms\Components\Select::make('type')
                        ->label('Tipe')
                        ->options([
                            'gift_card' => 'Gift Card',
                            'corporate' => 'Corporate Account',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            GiftCard::STATUS_ACTIVE => 'Aktif',
                            GiftCard::STATUS_INACTIVE => 'Nonaktif',
                            GiftCard::STATUS_SUSPENDED => 'Suspended',
                            GiftCard::STATUS_EXHAUSTED => 'Habis',
                            GiftCard::STATUS_EXPIRED => 'Expired',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('initial_value')
                        ->label('Nilai Awal')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Saat pembuatan, saldo akan mengikuti nilai ini.'),
                    Forms\Components\TextInput::make('balance')
                        ->label('Sisa Saldo')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Gunakan aksi Reload untuk menambah saldo.'),
                    Forms\Components\DatePicker::make('expires_at')
                        ->label('Berlaku Sampai')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection(),
                ])->columns(3),
            Forms\Components\Section::make('Penerima / Perusahaan')
                ->schema([
                    Forms\Components\TextInput::make('issued_to_name')->label('Nama Penerima'),
                    Forms\Components\TextInput::make('issued_to_email')->label('Email Penerima')->email(),
                    Forms\Components\TextInput::make('company_name')->label('Nama Perusahaan'),
                    Forms\Components\TextInput::make('company_contact')->label('Kontak Perusahaan'),
                ])->columns(2),
            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        GiftCard::STATUS_ACTIVE => 'success',
                        GiftCard::STATUS_EXHAUSTED => 'gray',
                        GiftCard::STATUS_EXPIRED => 'danger',
                        GiftCard::STATUS_SUSPENDED => 'warning',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        GiftCard::STATUS_ACTIVE => 'Aktif',
                        GiftCard::STATUS_SUSPENDED => 'Suspended',
                        GiftCard::STATUS_EXHAUSTED => 'Habis',
                        GiftCard::STATUS_EXPIRED => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reload')
                    ->label('Reload Saldo')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->visible(fn (GiftCard $record) => $record->status !== GiftCard::STATUS_EXPIRED)
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Top-up')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (GiftCard $record, array $data) {
                        app(\App\Services\GiftCardService::class)->reload($record, (float) $data['amount'], $data['notes'] ?? null);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGiftCards::route('/'),
            'create' => Pages\CreateGiftCard::route('/create'),
            'edit' => Pages\EditGiftCard::route('/{record}/edit'),
        ];
    }
}
