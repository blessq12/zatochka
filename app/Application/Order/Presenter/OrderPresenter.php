<?php

namespace App\Application\Order\Presenter;

use App\Application\Order\DTO\OrderDTO;

interface OrderPresenter
{
    /** @return array<string, mixed> */
    public function present(OrderDTO $order): array;

    /**
     * @param list<OrderDTO> $orders
     * @return list<array<string, mixed>>
     */
    public function presentCollection(array $orders): array;
}
