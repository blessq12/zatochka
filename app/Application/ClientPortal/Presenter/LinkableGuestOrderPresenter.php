<?php

namespace App\Application\ClientPortal\Presenter;

use App\Domain\OrderFulfillment\Entity\Order;

final class LinkableGuestOrderPresenter
{
    public static function label(Order $order): string
    {
        $snapshot = $order->clientSnapshot();
        $name = $snapshot?->fullName() ?? 'Без имени';
        $phone = $snapshot?->phone() ?? '—';

        return sprintf('%s · %s · %s', $order->orderNumber()->value, $name, $phone);
    }

    /**
     * @param  list<Order>  $orders
     * @return array<int, string>
     */
    public static function options(array $orders): array
    {
        $options = [];

        foreach ($orders as $order) {
            $orderId = $order->id();
            if ($orderId === null) {
                continue;
            }

            $options[$orderId] = self::label($order);
        }

        return $options;
    }
}
