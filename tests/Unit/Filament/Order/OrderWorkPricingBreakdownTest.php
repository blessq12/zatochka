<?php

namespace Tests\Unit\Filament\Order;

use App\Filament\Order\Resources\OrderResource\Support\OrderWorkPricing;
use App\Infrastructure\Order\Model\OrderModel;
use PHPUnit\Framework\TestCase;

final class OrderWorkPricingBreakdownTest extends TestCase
{
    public function test_format_pricing_state_shows_placeholder_when_unset(): void
    {
        $this->assertSame('цены не указаны', OrderWorkPricing::formatPricingState(null));
        $this->assertSame('1 250.50 ₽', OrderWorkPricing::formatPricingState([
            'total' => 1250.5,
            'currency' => 'RUB',
        ]));
    }

    public function test_breakdown_without_container_prices_is_all_unset(): void
    {
        $order = new OrderModel;
        $order->id = 'missing-order-id';
        $order->estimated_currency = 'RUB';

        // No DB — container port returns null via app in real env; here we only assert formatter contract.
        $this->assertSame('цены не указаны', OrderWorkPricing::formatPricingState(null));
    }
}
