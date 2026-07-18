<?php

namespace Tests\Unit\Domain\Inventory;

use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\StockSku;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class StockItemReverseWriteOffTest extends TestCase
{
    public function test_reverse_restores_quantity_and_marks_reversal(): void
    {
        $item = $this->stockItem('10');
        $item->writeOff(
            new EntityId(10),
            new Quantity('3'),
            orderId: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            unitPrice: new Money('50.00'),
        );

        $item->reverseWriteOff(new EntityId(11), new EntityId(10));

        $this->assertSame('10.000', $item->quantityOnHand()->value);
        $this->assertTrue($item->isMovementReversed(new EntityId(10)));
        $this->assertSame(MovementType::Reversal, $item->movements()[1]->type);
        $this->assertSame(10, $item->movements()[1]->reversesMovementId?->value);
    }

    public function test_cannot_reverse_twice(): void
    {
        $item = $this->stockItem('10');
        $item->writeOff(
            new EntityId(10),
            new Quantity('1'),
            orderId: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            unitPrice: new Money('50.00'),
        );
        $item->reverseWriteOff(new EntityId(11), new EntityId(10));

        $this->expectException(DomainException::class);
        $item->reverseWriteOff(new EntityId(12), new EntityId(10));
    }

    public function test_replace_via_reverse_then_write_off(): void
    {
        $item = $this->stockItem('10');
        $item->writeOff(
            new EntityId(10),
            new Quantity('2'),
            orderId: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            orderItemId: 5,
            unitPrice: new Money('100.00'),
        );

        $item->reverseWriteOff(new EntityId(11), new EntityId(10));
        $item->writeOff(
            new EntityId(12),
            new Quantity('4'),
            orderId: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            orderItemId: 5,
            unitPrice: new Money('120.00'),
        );

        $this->assertSame('6.000', $item->quantityOnHand()->value);
        $this->assertSame('120.00', $item->findMovement(new EntityId(12))->unitPrice?->amount);
        $this->assertTrue($item->isMovementReversed(new EntityId(10)));
    }

    private function stockItem(string $initial): StockItem
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
