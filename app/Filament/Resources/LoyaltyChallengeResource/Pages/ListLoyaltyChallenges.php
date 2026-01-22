<?php

namespace App\Filament\Resources\LoyaltyChallengeResource\Pages;

use App\Filament\Resources\LoyaltyChallengeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyChallenges extends ListRecords
{
    protected static string $resource = LoyaltyChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
