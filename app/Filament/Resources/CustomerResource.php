<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\CustomerPointTransactionsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Models\Customer;
use App\Support\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Customers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profil')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nama')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->nullable(),
                        Forms\Components\TextInput::make('phone')->label('No. Telp')->tel()->nullable(),
                        Forms\Components\Select::make('preferred_channel')
                            ->label('Channel Favorit')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'phone' => 'Telepon',
                            ])->nullable(),
                        Forms\Components\TagsInput::make('preferences')
                            ->label('Preferensi')
                            ->placeholder('Misal: tanpa gula, hot, latte art'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('No. Telp')->searchable(),
                Tables\Columns\TextColumn::make('points')->label('Poin')->sortable(),
                Tables\Columns\TextColumn::make('lifetime_value')->label('Lifetime Value')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('last_order_at')->label('Order Terakhir')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('high_value')
                    ->label('Lifetime Value > 5jt')
                    ->query(fn ($query) => $query->where('lifetime_value', '>=', 5_000_000)),
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

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            CustomerPointTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canViewAny(): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canCreate(): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canEdit($record): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canDelete($record): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canDeleteAny(): bool
    {
        return Feature::enabled('loyalty');
    }
}
