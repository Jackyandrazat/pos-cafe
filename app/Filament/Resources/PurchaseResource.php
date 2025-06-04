<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Pembelian Bahan Baku';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
           ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pembeli/Admin')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\TextInput::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->maxLength(100),

                Forms\Components\DatePicker::make('purchase_date')
                    ->label('Tanggal Pembelian')
                    ->default(now())
                    ->required(),

                Forms\Components\Repeater::make('items')
                    ->label('Detail Pembelian')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('ingredient_id')
                            ->label('Bahan Baku')
                            ->relationship('ingredient', 'name')
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('price_per_unit')
                            ->label('Harga per Satuan')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required(),
                    ])
                    ->columns(4)
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Pembeli')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice')->sortable(),

                Tables\Columns\TextColumn::make('purchase_date')->label('Tanggal')->date()->sortable(),

                Tables\Columns\TextColumn::make('total_amount')->label('Total (Rp)')->money('IDR')->sortable(),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
