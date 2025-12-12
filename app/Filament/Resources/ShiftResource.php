<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Shift;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Exports\ShiftExporter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\ShiftResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShiftResource\RelationManagers;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Shift Kasir';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\DateTimePicker::make('shift_open_time')
                    ->label('Waktu Mulai Shift')
                    ->required(),

                Forms\Components\DateTimePicker::make('shift_close_time')
                    ->label('Waktu Akhir Shift')
                    ->nullable(),

                Forms\Components\TextInput::make('opening_balance')
                    ->label('Modal Awal')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('closing_balance')
                    ->label('Saldo Akhir')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('total_sales')
                    ->label('Total Penjualan')
                    ->numeric()
                    ->disabled()
                    ->dehydrateStateUsing(
                        fn ($state, $get) =>
                        // otomatis hitung total penjualan dari transaksi yang tercatat
                        $get('payments')->sum('amount_paid') ?? 0
                    ),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shift_open_time')
                    ->label('Waktu Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shift_close_time')
                    ->label('Waktu Akhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Modal Awal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('closing_balance')
                    ->label('Saldo Akhir')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payments.sum')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->payments->sum('amount_paid'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->options(User::all()->pluck('name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ShiftExporter::class),
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
