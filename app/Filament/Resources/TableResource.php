<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Filament\Resources\TableResource\RelationManagers;
use App\Models\CafeTable as TableModel;
use App\Support\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Meja Cafe';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('area_id')
                    ->label('Area')
                    ->relationship('area', 'name')
                    ->required(),

                Forms\Components\TextInput::make('table_number')
                    ->label('Nomor Meja')
                    ->required()
                    ->maxLength(50),

                Forms\Components\Select::make('status')
                    ->label('Status Meja')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'reserved' => 'Reserved',
                        'cleaning' => 'Cleaning',
                    ])
                    ->default('available')
                    ->required(),

                Forms\Components\TextInput::make('capacity')
                    ->label('Kapasitas (pax)')
                    ->numeric()
                    ->minValue(1)
                    ->default(2),

                Forms\Components\Fieldset::make('Posisi Opsional')
                    ->schema([
                        Forms\Components\TextInput::make('x_position')
                            ->numeric()
                            ->label('Posisi X'),
                        Forms\Components\TextInput::make('y_position')
                            ->numeric()
                            ->label('Posisi Y'),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_number')
                    ->label('Nomor Meja')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('area.name')
                    ->label('Area')
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Pax')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'reserved',
                        'danger' => 'occupied',
                        'gray' => 'cleaning',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('table_management');
    }

    public static function canViewAny(): bool
    {
        return Feature::enabled('table_management');
    }

    public static function canCreate(): bool
    {
        return Feature::enabled('table_management');
    }

    public static function canEdit($record): bool
    {
        return Feature::enabled('table_management');
    }

    public static function canDelete($record): bool
    {
        return Feature::enabled('table_management');
    }

    public static function canDeleteAny(): bool
    {
        return Feature::enabled('table_management');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
