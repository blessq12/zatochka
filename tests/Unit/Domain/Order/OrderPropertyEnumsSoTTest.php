<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use PHPUnit\Framework\TestCase;

final class OrderPropertyEnumsSoTTest extends TestCase
{
    public function test_service_type_options_are_source_of_truth(): void
    {
        $this->assertSame([
            'sharpening' => 'Заточка',
            'repair' => 'Ремонт',
        ], OrderServiceType::options());
        $this->assertSame(['sharpening', 'repair'], OrderServiceType::values());
        $this->assertSame('Заточка', OrderServiceType::tryLabel('sharpening'));
        $this->assertNull(OrderServiceType::tryLabel('unknown'));
    }

    public function test_billing_type_options_are_source_of_truth(): void
    {
        $this->assertSame([
            'paid' => 'Платный',
            'warranty' => 'Гарантийный',
        ], OrderBillingType::options());
        $this->assertSame(['paid', 'warranty'], OrderBillingType::values());
        $this->assertSame('Гарантийный', OrderBillingType::tryLabel('warranty'));
        $this->assertNull(OrderBillingType::tryLabel(null));
    }

    public function test_urgency_options_are_source_of_truth(): void
    {
        $this->assertSame([
            'normal' => 'Обычный',
            'urgent' => 'Срочный',
        ], OrderUrgency::options());
        $this->assertSame(['normal', 'urgent'], OrderUrgency::values());
        $this->assertSame('Срочный', OrderUrgency::tryLabel('urgent'));
    }

    public function test_status_options_cover_all_cases(): void
    {
        $options = OrderStatus::options();

        $this->assertCount(count(OrderStatus::cases()), $options);
        $this->assertSame(OrderStatus::values(), array_keys($options));
        $this->assertSame('Готов к выдаче', OrderStatus::tryLabel('ready'));
        $this->assertSame('info', OrderStatus::tryColor('ready'));
        $this->assertSame('danger', OrderStatus::Cancelled->color());
        $this->assertNull(OrderStatus::tryLabel('nope'));
    }

    public function test_item_status_options_are_source_of_truth(): void
    {
        $this->assertSame([
            'accepted' => 'Принят',
            'in_production' => 'В производстве',
            'completed' => 'Готов',
            'rejected' => 'Отклонён',
            'issued' => 'Выдан',
        ], OrderItemStatus::options());
        $this->assertSame(OrderItemStatus::values(), array_keys(OrderItemStatus::options()));
    }
}
