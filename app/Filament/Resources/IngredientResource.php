<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Filament\Resources\IngredientResource\RelationManagers;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Bahan Baku';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Bahan')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('stock_qty')
                ->label('Stok')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('unit')
                ->label('Satuan')
                ->placeholder('gram, ml, pcs')
                ->maxLength(20)
                ->required(),

            Forms\Components\TextInput::make('price_per_unit')
                ->label('Harga per Satuan')
                ->numeric()
                ->prefix('Rp')
                ->default(0),

            Forms\Components\DatePicker::make('expired')
                ->label('Tanggal Expired')
                ->nullable(),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('price_per_unit')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expired')
                    ->label('Expired')
                    ->date('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    // Uncomment this if you want to disable soft delete
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery();
    // }

}
