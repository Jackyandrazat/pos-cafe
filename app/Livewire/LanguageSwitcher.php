<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public array $locales = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public string $locale;
    public string $returnUrl = '';

    public function mount(): void
    {
        $this->locale = session('app_locale', config('app.locale', 'id'));
        $this->returnUrl = url()->current();
    }

    public function switchLocale(string $locale): void
    {
        if (! array_key_exists($locale, $this->locales)) {
            return;
        }

        session(['app_locale' => $locale]);
        App::setLocale($locale);
        Config::set('app.locale', $locale);

        $this->locale = $locale;

        $this->dispatch('language-changed', locale: $locale);

        $this->redirect($this->returnUrl);
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
