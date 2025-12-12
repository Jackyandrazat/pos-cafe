<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Nama Produk')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            Forms\Components\Repeater::make('ingredients')
                ->label('Komposisi Bahan')
                ->relationship()
                ->schema([
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
                ])
                ->columns(3)
                ->required(),

            Forms\Components\TextInput::make('price')
                ->label('Harga Jual')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('cost_price')
                ->label('Harga Modal')
                ->numeric()
                ->nullable(),

            Forms\Components\TextInput::make('stock_qty')
                ->label('Stok')
                ->numeric()
                ->default(0),

            Forms\Components\Textarea::make('description')
                ->label('Deskripsi')
                ->nullable(),

            Forms\Components\Toggle::make('status_enabled')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('status_enabled')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                    ->exporter(ProductExporter::class)
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

}
