<?php

namespace App\Filament\Resources\LoyaltyChallengeResource\Pages;

use App\Filament\Resources\LoyaltyChallengeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyChallenge extends EditRecord
{
    protected static string $resource = LoyaltyChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
