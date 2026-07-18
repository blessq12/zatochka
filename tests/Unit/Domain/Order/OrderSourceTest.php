<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\VO\OrderSource;
use PHPUnit\Framework\TestCase;

final class OrderSourceTest extends TestCase
{
    public function test_options_cover_all_cases(): void
    {
        $options = OrderSource::options();

        $this->assertSame('Сайт', $options[OrderSource::Website->value]);
        $this->assertSame('Админка', $options[OrderSource::Admin->value]);
        $this->assertSame('API', $options[OrderSource::Api->value]);
        $this->assertCount(count(OrderSource::cases()), $options);
        $this->assertSame('info', OrderSource::Website->color());
        $this->assertSame('primary', OrderSource::Admin->color());
        $this->assertSame('gray', OrderSource::Api->color());
    }
}
