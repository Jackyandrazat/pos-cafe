<?php

namespace App\Filament\Resources\IngredientWasteResource\Pages;

use App\Filament\Resources\IngredientWasteResource;
use App\Models\Ingredient;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateIngredientWaste extends CreateRecord
{
    protected static string $resource = IngredientWasteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        $data['recorded_at'] = $data['recorded_at'] ?? now();

        if (! ($data['unit'] ?? null) && ($data['ingredient_id'] ?? null)) {
            $data['unit'] = Ingredient::find($data['ingredient_id'])?->unit;
        }

        return $data;
    }
}
