<?php

namespace App\Application\OrderFulfillment\ReadModel;

/**
 * Проекция для Blade-шаблонов PDF.
 */
final class OrderDocumentData
{
  /**
   * @param  list<array{type: string, quantity: int}>  $tools
   * @param  list<array{description: string, price: float, equipment_component_serial_number?: string}>  $works
   * @param  list<array{name: string, quantity: string, price: float}>  $materials
   */
    public function __construct(
        public string $orderNumber,
        public string $orderDate,
        public string $serviceTypeLabel,
        public ?string $urgency,
        public string $branchName,
        public ?string $branchAddress,
        public ?string $branchPhone,
        public string $clientName,
        public string $clientPhone,
        public ?string $equipmentName,
        public array $tools,
        public ?string $problemDescription,
        public ?float $price,
        public ?string $managerName,
        public ?string $masterName,
        public ?string $companyName,
        public ?string $companyLegalName,
        public ?string $companyInn,
        public ?string $companyKpp,
        public ?string $companyOgrn,
        public ?string $companyAddress,
        public ?string $companyPhone,
        public array $works = [],
        public array $materials = [],
    ) {}
}
