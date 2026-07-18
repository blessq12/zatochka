<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\VO\SharpeningToolType;
use PHPUnit\Framework\TestCase;

final class SharpeningToolTypeTest extends TestCase
{
    public function test_options_are_source_of_truth_for_ui(): void
    {
        $options = SharpeningToolType::options();

        $this->assertSame('Кухонный нож', $options['kitchen_knife']);
        $this->assertSame('Другое', $options['other']);
        $this->assertCount(count(SharpeningToolType::cases()), $options);
        $this->assertSame(SharpeningToolType::values(), array_keys($options));
    }

    public function test_try_label(): void
    {
        $this->assertSame('Шеф-нож', SharpeningToolType::tryLabel('chef_knife'));
        $this->assertNull(SharpeningToolType::tryLabel('unknown_tool'));
        $this->assertNull(SharpeningToolType::tryLabel(null));
    }
}
