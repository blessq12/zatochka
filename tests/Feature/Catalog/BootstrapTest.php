<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_отдаёт_цены_и_настройки(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $response = $this->getJson('/api/bootstrap');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'prices',
                    'contacts',
                    'schedule',
                    'delivery_info',
                    'company',
                ],
            ]);

        $prices = $response->json('data.prices');
        $this->assertNotEmpty($prices);
        $this->assertSame('Заточка инструмента', $prices[0]['title']);
        $this->assertSame('info@zatochka.tsk', $response->json('data.contacts.email'));
    }
}
