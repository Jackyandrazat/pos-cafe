<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChallengeProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'challengeProgresses';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Misi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('challenge.type')
                    ->label('Tipe')
                    ->badge(),
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state, $record) => sprintf('%d / %d', $state, $record->challenge?->target_value ?? 0)),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'rewarded',
                        'warning' => 'in_progress',
                        'info' => 'completed',
                        'gray' => 'available',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'rewarded' => 'Badge diberikan',
                        'completed' => 'Siap klaim',
                        'in_progress' => 'Sedang berjalan',
                        default => 'Belum mulai',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
