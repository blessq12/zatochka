<?php

namespace App\Domain\Crm\Aggregate;

use App\Domain\Crm\Entity\EquipmentModule;
use App\Shared\Domain\DomainException;

final class Equipment
{
    /**
     * @param  list<EquipmentModule>  $modules
     */
    public function __construct(
        private ?int $id,
        private int $clientId,
        private string $name,
        private string $brand,
        private string $type,
        private array $modules = [],
        private bool $deleted = false,
    ) {
        $this->assertInvariants($name, $brand, $type, $modules);
    }

    /**
     * @param  list<EquipmentModule>  $modules
     */
    public static function create(
        int $clientId,
        string $name,
        string $brand,
        string $type,
        array $modules = [],
    ): self {
        return new self(null, $clientId, $name, $brand, $type, $modules);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function brand(): string
    {
        return $this->brand;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return list<EquipmentModule>
     */
    public function modules(): array
    {
        return $this->modules;
    }

    public function changeDetails(string $name, string $brand, string $type): void
    {
        $this->assertInvariants($name, $brand, $type, $this->modules);
        $this->name = $name;
        $this->brand = $brand;
        $this->type = $type;
    }

    /**
     * @param  list<EquipmentModule>  $modules
     */
    public function replaceModules(array $modules): void
    {
        $this->assertUniqueSerials($modules);
        $this->modules = $modules;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function markDeleted(): void
    {
        $this->deleted = true;
    }

    /**
     * @param  list<EquipmentModule>  $modules
     */
    private function assertInvariants(string $name, string $brand, string $type, array $modules): void
    {
        if (trim($name) === '' || trim($brand) === '' || trim($type) === '') {
            throw new DomainException('Equipment name, brand and type are required.');
        }

        $this->assertUniqueSerials($modules);
    }

    /**
     * @param  list<EquipmentModule>  $modules
     */
    private function assertUniqueSerials(array $modules): void
    {
        $seen = [];

        foreach ($modules as $module) {
            if (! $module instanceof EquipmentModule) {
                throw new DomainException('Invalid equipment module.');
            }

            if (trim($module->name()) === '' || trim($module->serialNumber()) === '') {
                throw new DomainException('Module name and serial number are required.');
            }

            $serial = $module->serialNumber();
            if (isset($seen[$serial])) {
                throw new DomainException('Module serial numbers must be unique within equipment.');
            }

            $seen[$serial] = true;
        }
    }
}
