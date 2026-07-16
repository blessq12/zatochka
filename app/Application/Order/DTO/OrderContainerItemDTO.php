<?php

namespace App\Application\Order\DTO;

/**
 * @param list<array{
 *     id:int,
 *     description:string,
 *     created_at:string,
 *     order_item_id:int,
 *     equipment_component_id:?int,
 *     component_name:?string,
 *     price:array{unit_amount:string,line_amount:string,currency:string,calculated:bool}|null
 * }> $works
 * @param list<array{id:int,stock_item_id:int,quantity:string,comment:?string,created_at:?string}> $materials
 * @param array{id:int,unit_amount:string,base_amount:string,currency:string,calculated:bool}|null $estimate
 */
final readonly class OrderContainerItemDTO
{
    public function __construct(
        public int $id,
        public ?int $clientEquipmentId,
        public ?string $toolName,
        public ?string $toolType,
        public ?int $quantity,
        public int $rejectedQuantity,
        public int $repairableQuantity,
        public ?string $rejectionReason,
        public string $status,
        public array $works,
        public array $materials,
        public ?array $estimate,
    ) {}
}
