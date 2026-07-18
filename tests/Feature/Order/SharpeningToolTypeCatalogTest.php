<?php

namespace Tests\Feature\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SharpeningToolTypeCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_returns_domain_enum_values(): void
    {
        $response = $this->getJson('/api/public/sharpening-tool-types');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['value', 'label'],
                ],
            ]);

        $values = collect($response->json('data'))->pluck('value')->all();

        $this->assertContains('kitchen_knife', $values);
        $this->assertContains('manicure_tool', $values);
        $this->assertContains('other', $values);
        $this->assertNotContains('manicure', $values);
        $this->assertNotContains('hair', $values);
    }
}
