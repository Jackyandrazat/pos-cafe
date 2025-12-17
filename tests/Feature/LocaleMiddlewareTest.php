<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/locale-check', function () {
            return app()->getLocale();
        });
    }

    public function test_locale_defaults_to_application_locale(): void
    {
        $response = $this->get('/locale-check');

        $response->assertOk();
        $response->assertSee(config('app.locale'));
    }

    public function test_locale_respects_session_value(): void
    {
        $response = $this->withSession(['app_locale' => 'en'])->get('/locale-check');

        $response->assertOk();
        $response->assertSee('en');
    }
}
