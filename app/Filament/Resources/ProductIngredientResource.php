<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductIngredientResource\Pages;
use App\Filament\Resources\ProductIngredientResource\RelationManagers;
use App\Models\ProductIngredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductIngredientResource extends Resource
{
    protected static ?string $model = ProductIngredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Relasi Produk & Bahan';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
         return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->relationship('product', 'name')
                ->required(),

            Forms\Components\Select::make('ingredient_id')
                ->label('Bahan Baku')
                ->relationship('ingredient', 'name')
                ->required(),

            Forms\Components\TextInput::make('quantity_used')
                ->label('Jumlah Digunakan')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('unit')
                ->label('Satuan')
                ->placeholder('gram, ml, pcs')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('ingredient.name')
                ->label('Bahan Baku')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('quantity_used')
                ->label('Jumlah'),

            Tables\Columns\TextColumn::make('unit')
                ->label('Satuan'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
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
            'index' => Pages\ListProductIngredients::route('/'),
            'create' => Pages\CreateProductIngredient::route('/create'),
            'edit' => Pages\EditProductIngredient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
