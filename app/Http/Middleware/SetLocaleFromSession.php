<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = ['id', 'en'];
        $defaultLocale = config('app.locale', 'id');

        $locale = session('app_locale', $defaultLocale);

        if (! in_array($locale, $availableLocales, true)) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);
        Config::set('app.locale', $locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
