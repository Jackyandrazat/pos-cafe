<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChallengeAwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'challengeAwards';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Asal Misi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge_name')
                    ->label('Badge')
                    ->badge()
                    ->suffix(fn ($record) => $record->badge_code ? ' • ' . $record->badge_code : ''),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label('Poin')
                    ->suffix(' pts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('awarded_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('awarded_at', 'desc');
    }
}
