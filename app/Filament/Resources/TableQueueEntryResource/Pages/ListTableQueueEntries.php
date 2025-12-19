<?php

namespace App\Filament\Resources\TableQueueEntryResource\Pages;

use App\Filament\Resources\TableQueueEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableQueueEntries extends ListRecords
{
    protected static string $resource = TableQueueEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
