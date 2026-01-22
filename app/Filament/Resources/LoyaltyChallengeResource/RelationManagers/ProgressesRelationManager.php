<?php

namespace App\Filament\Resources\LoyaltyChallengeResource\RelationManagers;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'progresses';

    public function table(Table $table): Table
    {
        $challenge = $this->ownerRecord;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Member')
                    ->searchable()
                    ->url(fn ($record) => CustomerResource::getUrl('edit', ['record' => $record->customer_id]))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state . ' / ' . ($challenge->target_value ?? '-')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'rewarded' => 'primary',
                        'in_progress' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('window_start')
                    ->label('Periode')
                    ->formatStateUsing(fn ($state, $record) => $record->window_start
                        ? sprintf('%s - %s',
                            $record->window_start?->format('d M'),
                            $record->window_end?->format('d M')
                        )
                        : '-'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('progress_state')
                    ->label('Status Progress')
                    ->options([
                        'rewarded' => 'Badge sudah diberikan',
                        'completed' => 'Selesai menunggu badge',
                        'in_progress' => 'Sedang berjalan',
                        'available' => 'Belum mulai',
                    ])
                    ->query(function ($query, ?string $state) {
                        if ($state === 'rewarded') {
                            return $query->whereNotNull('rewarded_at');
                        }

                        if ($state === 'completed') {
                            return $query->whereNull('rewarded_at')->whereNotNull('completed_at');
                        }

                        if ($state === 'in_progress') {
                            return $query->whereNull('completed_at')->where('current_value', '>', 0);
                        }

                        if ($state === 'available') {
                            return $query->where('current_value', 0);
                        }

                        return $query;
                    }),
            ]);
    }
}
