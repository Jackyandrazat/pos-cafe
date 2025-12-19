<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientWasteResource\Pages;
use App\Models\Ingredient;
use App\Models\IngredientWaste;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientWasteResource extends Resource
{
    protected static ?string $model = IngredientWaste::class;

    protected static ?string $navigationGroup = 'Persediaan';

    protected static ?string $navigationIcon = 'heroicon-o-trash';

    protected static ?string $navigationLabel = 'Waste Bahan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('ingredient_id')
                            ->label('Bahan')
                            ->relationship('ingredient', 'name')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $unit = Ingredient::find($state)?->unit;
                                $set('unit', $unit);
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->default(null)
                            ->helperText('Terisi otomatis mengikuti master bahan.'),
                        Forms\Components\Select::make('reason')
                            ->label('Alasan')
                            ->options([
                                'expired' => 'Expired',
                                'spillage' => 'Tumpah/Rusak',
                                'production_error' => 'Kesalahan produksi',
                                'other' => 'Lainnya',
                            ])
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Log')
                    ->schema([
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Waktu Pencatatan')
                            ->seconds(false)
                            ->default(fn () => now()),
                        Forms\Components\Select::make('shift_id')
                            ->relationship('shift', 'id')
                            ->label('Shift')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'Shift #' . $record->id)
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, IngredientWaste $record) => number_format($state, 2) . ' ' . ($record->unit ?? $record->ingredient->unit ?? '')),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('recorded_at')
                    ->form([
                        Forms\Components\DatePicker::make('start')->label('Dari'),
                        Forms\Components\DatePicker::make('end')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start'], fn ($q, $date) => $q->whereDate('recorded_at', '>=', $date))
                            ->when($data['end'], fn ($q, $date) => $q->whereDate('recorded_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredientWastes::route('/'),
            'create' => Pages\CreateIngredientWaste::route('/create'),
            'edit' => Pages\EditIngredientWaste::route('/{record}/edit'),
        ];
    }
}
