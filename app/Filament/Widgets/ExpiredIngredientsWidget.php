<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Widgets\Widget;

class ExpiredIngredientsWidget extends Widget
{
    protected static ?string $heading = 'Expired & Soon Expired Ingredients';

    protected static string $view = 'filament.widgets.expired-ingredients-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'expiredIngredients' => Ingredient::whereDate('expired', '<=', now())->get(),
            'soonExpiredIngredients' => Ingredient::whereBetween('expired', [now()->addDay(), now()->addDays(7)])->get(),
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view(static::$view, $this->getViewData());
    }
}
