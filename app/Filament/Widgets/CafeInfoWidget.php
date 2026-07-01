<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CafeInfoWidget extends Widget
{
    // Urutan widget di dashboard. -2 menempatkannya di paling atas (sama seperti default info widget).
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.cafe-info-widget';

    public function getViewData(): array
    {
        return [
            'cafeName' => 'POS Cafe Maju Mundur Cantik',
            'version' => 'v1.2.0',
            'developer' => 'kodeeweb.com',
            'buildDate' => '2026',
        ];
    }
}
