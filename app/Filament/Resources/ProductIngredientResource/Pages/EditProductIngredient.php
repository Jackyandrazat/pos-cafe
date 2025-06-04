<?php

namespace App\Filament\Resources\ProductIngredientResource\Pages;

use App\Filament\Resources\ProductIngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductIngredient extends EditRecord
{
    protected static string $resource = ProductIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
