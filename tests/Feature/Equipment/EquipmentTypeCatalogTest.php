<?php

namespace Tests\Feature\Equipment;

use App\Domain\Equipment\VO\EquipmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EquipmentTypeCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_returns_domain_enum_values(): void
    {
        $response = $this->getJson('/api/public/equipment-types');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['value', 'label'],
                ],
            ]);

        $values = collect($response->json('data'))->pluck('value')->all();
        $labels = collect($response->json('data'))->pluck('label', 'value')->all();

        $this->assertSame(EquipmentType::values(), $values);
        $this->assertSame('Машинка для стрижки', $labels['clipper']);
        $this->assertSame('Другое', $labels['other']);
    }
}
