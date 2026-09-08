<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTables extends ListRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_all_qr')
                ->label('Cetak Semua QR Meja')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn (): string => route('tables.qr.print-all'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
