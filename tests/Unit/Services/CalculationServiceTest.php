<?php

namespace Tests\Unit\Services;

use App\Services\CalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalculationService $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculationService = new CalculationService();
    }

    /** @test */
    public function it_calculates_sharpening_price_correctly()
    {
        // Act
        $result = $this->calculationService->calculateSharpeningPrice('manicure', 2, false);

        // Assert
        $this->assertEquals(500, $result['base_price']);
        $this->assertEquals(2, $result['tools_count']);
        $this->assertEquals(1000, $result['subtotal']);
        $this->assertEquals(0, $result['delivery_cost']);
        $this->assertEquals(1000, $result['total']);
    }

    /** @test */
    public function it_calculates_sharpening_price_with_delivery()
    {
        // Act
        $result = $this->calculationService->calculateSharpeningPrice('hair', 1, true);

        // Assert
        $this->assertEquals(800, $result['base_price']);
        $this->assertEquals(1, $result['tools_count']);
        $this->assertEquals(800, $result['subtotal']);
        $this->assertEquals(300, $result['delivery_cost']);
        $this->assertEquals(1100, $result['total']);
    }

    /** @test */
    public function it_calculates_repair_price_correctly()
    {
        // Act
        $result = $this->calculationService->calculateRepairPrice('manicure', 'Простой ремонт', false);

        // Assert
        $this->assertEquals(1000, $result['base_price']);
        $this->assertEquals(1.0, $result['complexity_multiplier']);
        $this->assertEquals(1000, $result['subtotal']);
        $this->assertEquals(0, $result['delivery_cost']);
        $this->assertEquals(1000, $result['total']);
    }

    /** @test */
    public function it_calculates_repair_price_with_complexity()
    {
        // Act
        $result = $this->calculationService->calculateRepairPrice('hair', 'Сложный ремонт', false);

        // Assert
        $this->assertEquals(1200, $result['base_price']);
        $this->assertEquals(1.5, $result['complexity_multiplier']);
        $this->assertEquals(1800, $result['subtotal']);
        $this->assertEquals(0, $result['delivery_cost']);
        $this->assertEquals(1800, $result['total']);
    }

    /** @test */
    public function it_calculates_repair_price_with_delivery()
    {
        // Act
        $result = $this->calculationService->calculateRepairPrice('grooming', 'Замена деталей', true);

        // Assert
        $this->assertEquals(1100, $result['base_price']);
        $this->assertEquals(1.3, $result['complexity_multiplier']);
        $this->assertEquals(1430, $result['subtotal']);
        $this->assertEquals(300, $result['delivery_cost']);
        $this->assertEquals(1730, $result['total']);
    }

    /** @test */
    public function it_calculates_discount_correctly()
    {
        // Act
        $result = $this->calculationService->calculateWithDiscount(1000, 10);

        // Assert
        $this->assertEquals(1000, $result['original_price']);
        $this->assertEquals(10, $result['discount_percent']);
        $this->assertEquals(100, $result['discount_amount']);
        $this->assertEquals(900, $result['final_price']);
    }

    /** @test */
    public function it_calculates_discount_with_zero_percent()
    {
        // Act
        $result = $this->calculationService->calculateWithDiscount(1000, 0);

        // Assert
        $this->assertEquals(1000, $result['original_price']);
        $this->assertEquals(0, $result['discount_percent']);
        $this->assertEquals(0, $result['discount_amount']);
        $this->assertEquals(1000, $result['final_price']);
    }

    /** @test */
    public function it_returns_all_prices()
    {
        // Act
        $prices = $this->calculationService->getAllPrices();

        // Assert
        $this->assertIsArray($prices);
        $this->assertArrayHasKey('sharpening', $prices);
        $this->assertArrayHasKey('repair', $prices);
        $this->assertArrayHasKey('delivery', $prices);

        $this->assertArrayHasKey('manicure', $prices['sharpening']);
        $this->assertArrayHasKey('hair', $prices['sharpening']);
        $this->assertArrayHasKey('grooming', $prices['sharpening']);

        $this->assertEquals(500, $prices['sharpening']['manicure']);
        $this->assertEquals(800, $prices['sharpening']['hair']);
        $this->assertEquals(700, $prices['sharpening']['grooming']);

        $this->assertEquals(1000, $prices['repair']['manicure']);
        $this->assertEquals(1200, $prices['repair']['hair']);
        $this->assertEquals(1100, $prices['repair']['grooming']);

        $this->assertEquals(300, $prices['delivery']);
    }

    /** @test */
    public function it_handles_unknown_tool_types()
    {
        // Act
        $result = $this->calculationService->calculateSharpeningPrice('unknown', 1, false);

        // Assert
        $this->assertEquals(600, $result['base_price']); // default price
        $this->assertEquals(600, $result['total']);
    }

    /** @test */
    public function it_handles_unknown_equipment_types()
    {
        // Act
        $result = $this->calculationService->calculateRepairPrice('unknown', 'Ремонт', false);

        // Assert
        $this->assertEquals(1000, $result['base_price']); // default price
        $this->assertEquals(1000, $result['total']);
    }
}
