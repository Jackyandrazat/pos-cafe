<?php

namespace App\Filament\Resources\LoyaltyChallengeResource\RelationManagers;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'awards';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Member')
                    ->searchable()
                    ->url(fn ($record) => CustomerResource::getUrl('edit', ['record' => $record->customer_id]))
                    ->openUrlInNewTab(),
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
