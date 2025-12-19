<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableQueueEntryResource\Pages;
use App\Models\CafeTable;
use App\Models\TableQueueEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TableQueueEntryResource extends Resource
{
    protected static ?string $model = TableQueueEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Antrean Pelanggan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Tamu')
                    ->schema([
                        Forms\Components\TextInput::make('guest_name')->label('Nama Tamu')->required(),
                        Forms\Components\TextInput::make('party_size')->label('Jumlah Orang')->numeric()->minValue(1)->default(1),
                        Forms\Components\TextInput::make('contact')->label('Kontak')->tel()->nullable(),
                    ])->columns(3),
                Forms\Components\Section::make('Antrean')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'waiting' => 'Menunggu',
                                'called' => 'Dipanggil',
                                'seated' => 'Sudah Duduk',
                                'cancelled' => 'Batal',
                            ])
                            ->label('Status')
                            ->default('waiting')
                            ->required(),
                        Forms\Components\Select::make('assigned_table_id')
                            ->label('Meja Ditugaskan')
                            ->options(fn () => CafeTable::orderBy('table_number')->pluck('table_number', 'id'))
                            ->searchable()
                            ->nullable(),
                        Forms\Components\TextInput::make('estimated_wait_minutes')->label('Estimasi Tunggu (menit)')->numeric()->nullable(),
                        Forms\Components\Textarea::make('notes')->rows(3)->label('Catatan')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')->label('Tamu')->searchable(),
                Tables\Columns\TextColumn::make('party_size')->label('Orang'),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->colors([
                    'info' => 'waiting',
                    'warning' => 'called',
                    'success' => 'seated',
                    'gray' => 'cancelled',
                ]),
                Tables\Columns\TextColumn::make('table.table_number')->label('Meja'),
                Tables\Columns\TextColumn::make('check_in_at')->label('Check-in')->dateTime('H:i'),
            ])
            ->defaultSort('check_in_at')
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTableQueueEntries::route('/'),
            'create' => Pages\CreateTableQueueEntry::route('/create'),
            'edit' => Pages\EditTableQueueEntry::route('/{record}/edit'),
        ];
    }
}
