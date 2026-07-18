<?php

namespace Tests\Unit\Domain\Inventory;

use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\VO\Quantity;
use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\StockSku;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class StockItemWriteOffPricingTest extends TestCase
{
    public function test_write_off_to_order_requires_positive_unit_price(): void
    {
        $item = $this->stockItem();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unit price is required when writing off material to an order.');

        $item->writeOff(
            new EntityId(10),
            new Quantity('1'),
            orderId: 'ORD-1',
            unitPrice: null,
        );
    }

    public function test_write_off_to_order_rejects_zero_unit_price(): void
    {
        $item = $this->stockItem();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unit price is required when writing off material to an order.');

        $item->writeOff(
            new EntityId(10),
            new Quantity('1'),
            orderId: 'ORD-1',
            unitPrice: new Money('0.00'),
        );
    }

    public function test_write_off_to_order_snapshots_unit_price_and_line_amount(): void
    {
        $item = $this->stockItem(initial: '5');

        $item->writeOff(
            new EntityId(10),
            new Quantity('2'),
            orderId: 'ORD-1',
            orderItemId: 3,
            unitPrice: new Money('150.50'),
        );

        $movement = $item->movements()[0];
        $this->assertSame('150.50', $movement->unitPrice?->amount);
        $this->assertSame('301.00', $movement->lineAmount()?->amount);
        $this->assertSame('3.000', $item->quantityOnHand()->value);
    }

    public function test_write_off_without_order_allows_missing_unit_price(): void
    {
        $item = $this->stockItem(initial: '2');

        $item->writeOff(new EntityId(10), new Quantity('1'));

        $this->assertNull($item->movements()[0]->unitPrice);
        $this->assertSame('1.000', $item->quantityOnHand()->value);
    }

    public function test_material_rejects_negative_unit_price(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Material unit price cannot be negative.');

        new Material(
            new EntityId(1),
            new StockSku('CON-00001'),
            'Oil',
            UnitOfMeasure::Liter,
            StockCategory::Consumable,
            new Money('-1.00'),
        );
    }

    public function test_material_change_unit_price(): void
    {
        $material = new Material(
            new EntityId(1),
            new StockSku('CON-00001'),
            'Oil',
            UnitOfMeasure::Liter,
            StockCategory::Consumable,
            new Money('10.00'),
        );

        $material->changeUnitPrice(new Money('12.50'));

        $this->assertSame('12.50', $material->unitPrice()->amount);
    }

    private function stockItem(string $initial = '10'): StockItem
    {
        return StockItem::open(
            new EntityId(1),
            new Material(
                new EntityId(2),
                new StockSku('SPR-00001'),
                'Blade',
                UnitOfMeasure::Piece,
                StockCategory::SparePart,
                new Money('100.00'),
            ),
            new Quantity($initial),
        );
    }
}
