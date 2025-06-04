<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    public function afterCreate(): void
    {
        $record = $this->record;
        DB::transaction(function () use ($record) {
            $total = 0;

            foreach ($record->items as $item) {
                $ingredient = $item->ingredient;
                if ($ingredient) {
                    $ingredient->increment('stock_qty', $item->quantity);
                }
                $total += $item->quantity * $item->price_per_unit;
            }

            $record->update(['total_amount' => $total]);
        });
    }
}
