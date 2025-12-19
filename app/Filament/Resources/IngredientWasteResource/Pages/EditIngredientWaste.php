<?php

namespace App\Filament\Resources\IngredientWasteResource\Pages;

use App\Filament\Resources\IngredientWasteResource;
use App\Models\Ingredient;
use Filament\Resources\Pages\EditRecord;

class EditIngredientWaste extends EditRecord
{
    protected static string $resource = IngredientWasteResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! ($data['unit'] ?? null) && ($data['ingredient_id'] ?? null)) {
            $data['unit'] = Ingredient::find($data['ingredient_id'])?->unit;
        }

        return $data;
    }
}
