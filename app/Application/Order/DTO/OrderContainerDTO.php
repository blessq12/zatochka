<?php

namespace App\Application\Order\DTO;

/**
 * @param list<OrderContainerItemDTO> $items
 * @param array{id:int,status:string,master_id:?int}|null $productionTask
 * @param list<array{id:int,text:string,created_at:string}> $masterInternalComments
 */
final readonly class OrderContainerDTO
{
    public function __construct(
        public OrderDTO $order,
        public ?array $productionTask,
        public array $items,
        public array $masterInternalComments = [],
    ) {}
}
