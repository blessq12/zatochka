<?php

namespace App\Application\Workshop\DTO;

/**
 * Enriched production task card for master POS (one task per order).
 *
 * @param list<array{id:int,description:string,sort_order:int,created_at:string,order_item_id:int}> $works
 * @param list<array{id:int,text:string,created_at:string}> $masterInternalComments
 * @param list<array{id:int,tool_name:?string,tool_type:?string,quantity:?int,rejected_quantity:int,repairable_quantity:int,status:string,client_equipment_id:?int}> $items
 * @param list<array{tool_type:?string,name:?string,quantity:?int,rejected_quantity:int,repairable_quantity:int}> $toolsSummary
 * @param list<array{id:int,name:?string,brand:?string,model:?string,serial_numbers:array<string,string>}> $equipmentList
 */
final readonly class MasterProductionTaskCardDTO
{
    public function __construct(
        public int $id,
        public string $status,
        public string $posStatus,
        public ?int $masterId,
        public string $orderId,
        public string $orderNumber,
        public string $serviceType,
        public string $billingType,
        public string $urgency,
        public bool $deliveryRequired,
        public ?string $defects,
        public ?string $internalNotes,
        public string $createdAt,
        public ?string $clientName,
        public ?string $clientPhone,
        public array $items,
        public array $works,
        public array $masterInternalComments,
        public array $toolsSummary,
        public array $equipmentList,
        public ?string $subjectLine,
        public ?string $problemExcerpt,
    ) {}
}
