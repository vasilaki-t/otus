<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature test for the localized dashboard route guarded by the
 * setlocale middleware (PSR-15 SetLocaleMiddleware bridged into Laravel).
 */
class LocaleMiddlewareTest extends TestCase
{
    public function test_en_dashboard_uses_english_locale(): void
    {
        $this->get('/en/dashboard')
            ->assertOk()
            ->assertJson([
                'locale' => 'en',
                'message' => 'Welcome',
            ]);
    }

    public function test_ru_dashboard_uses_russian_locale(): void
    {
        $this->get('/ru/dashboard')
            ->assertOk()
            ->assertJson([
                'locale' => 'ru',
                'message' => 'Добро пожаловать',
            ]);
    }

    public function test_unsupported_locale_is_not_matched(): void
    {
        $this->get('/de/dashboard')->assertNotFound();
    }
}
