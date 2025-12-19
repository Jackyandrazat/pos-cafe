<?php

namespace App\Filament\Pages;

use App\Support\Feature;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class FeatureToggle extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $title = 'Feature Toggle';

    protected static string $view = 'filament.pages.feature-toggle';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'features' => collect(Feature::all())
                ->mapWithKeys(fn ($module, $key) => [$key => Feature::enabled($key)])
                ->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Modul Aplikasi')
                    ->description('Aktif/nonaktifkan modul sesuai kebutuhan rollout di outlet tertentu.')
                    ->schema($this->getFeatureSchema())
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFeatureSchema(): array
    {
        return collect(Feature::all())
            ->map(function (array $module, string $key) {
                return Forms\Components\Toggle::make("features.{$key}")
                    ->label($module['label'] ?? Str::of($key)->headline())
                    ->helperText($module['description'] ?? null);
            })
            ->values()
            ->all();
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $features = $state['features'] ?? [];

        foreach ($features as $key => $enabled) {
            if (array_key_exists($key, Feature::all())) {
                Feature::set($key, (bool) $enabled);
            }
        }

        Notification::make()
            ->title('Feature toggle diperbarui')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
