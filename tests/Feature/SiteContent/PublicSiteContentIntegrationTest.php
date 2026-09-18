<?php

namespace Tests\Feature\SiteContent;

use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicSiteContentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_site_content_from_database(): void
    {
        $this->seed(SiteContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Заточка.ТСК', false)
            ->assertSee('Профессиональная заточка инструментов', false)
            ->assertSee('Пн–Пт', false)
            ->assertSee('10:00–19:00', false)
            ->assertSee('Сколько длится заточка?', false);
    }

    public function test_legal_page_uses_document_from_database(): void
    {
        $this->seed(SiteContentSeeder::class);

        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Политика конфиденциальности', false)
            ->assertSee('Текст политики конфиденциальности', false);
    }

    public function test_prices_page_uses_price_items(): void
    {
        $this->seed(SiteContentSeeder::class);

        $this->get('/prices')
            ->assertOk()
            ->assertSee('Маникюрный инструмент', false)
            ->assertSee('Ремонт фрезера', false);
    }
}
