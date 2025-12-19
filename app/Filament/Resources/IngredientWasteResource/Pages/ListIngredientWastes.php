<?php

namespace App\Filament\Resources\IngredientWasteResource\Pages;

use App\Filament\Resources\IngredientWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIngredientWastes extends ListRecords
{
    protected static string $resource = IngredientWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
