<?php

namespace App\Filament\Resources\GiftCardResource\RelationManagers;

use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $recordTitleAttribute = 'type';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Nilai')
                    ->money('IDR'),
                TextColumn::make('balance_after')
                    ->label('Saldo Setelah')
                    ->money('IDR'),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('created_at', 'desc');
    }
}
