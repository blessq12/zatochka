<?php

namespace App\Application\Order\Support;

use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\OrderItemKind;
use App\Shared\Domain\DomainException;

final class OrderItemMapper
{
    /**
     * @param  list<array<string, mixed>>  $payload
     * @return list<OrderItem>
     */
    public static function fromPayload(array $payload): array
    {
        $items = [];
        foreach (array_values($payload) as $index => $row) {
            $kind = OrderItemKind::tryFrom((string) ($row['kind'] ?? ''));
            if ($kind === null) {
                throw new DomainException('Unknown order item kind.');
            }

            if ($kind === OrderItemKind::Sharpening) {
                $items[] = OrderItem::sharpening(
                    (string) ($row['title'] ?? ''),
                    (int) ($row['quantity'] ?? 0),
                    $index,
                );
                continue;
            }

            $items[] = OrderItem::repair(
                (int) ($row['equipment_id'] ?? 0),
                isset($row['problem']) ? (string) $row['problem'] : null,
                $index,
            );
        }

        return $items;
    }
}
