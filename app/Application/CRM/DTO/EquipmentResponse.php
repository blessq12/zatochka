<?php

namespace App\Application\Crm\DTO;

final readonly class EquipmentResponse
{
    /**
     * @param  list<array{id: int|null, name: string, serial_number: string}>  $modules
     */
    public function __construct(
        public int $id,
        public int $clientId,
        public ?string $clientName,
        public string $name,
        public string $brand,
        public string $type,
        public array $modules,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     client_id: int,
     *     client_name: string|null,
     *     name: string,
     *     brand: string,
     *     type: string,
     *     modules: list<array{id: int|null, name: string, serial_number: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
            'name' => $this->name,
            'brand' => $this->brand,
            'type' => $this->type,
            'modules' => $this->modules,
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     brand: string,
     *     type: string,
     *     modules: list<array{id: int|null, name: string, serial_number: string}>
     * }
     */
    public function toCatalogArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'type' => $this->type,
            'modules' => $this->modules,
        ];
    }
}
