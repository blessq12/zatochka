<?php

namespace Tests\Unit\Domain\Finance;

use App\Domain\Finance\VO\PaymentMethod;
use PHPUnit\Framework\TestCase;

final class PaymentMethodTest extends TestCase
{
    public function test_options_are_source_of_truth_for_ui(): void
    {
        $options = PaymentMethod::options();

        $this->assertSame([
            'cash' => 'Наличные',
            'card' => 'Карта',
            'transfer' => 'Перевод',
        ], $options);
        $this->assertSame(['cash', 'card', 'transfer'], PaymentMethod::values());
    }

    public function test_try_label(): void
    {
        $this->assertSame('Карта', PaymentMethod::tryLabel('card'));
        $this->assertNull(PaymentMethod::tryLabel('bitcoin'));
        $this->assertNull(PaymentMethod::tryLabel(null));
    }
}
