<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;

final class WorkEntry
{
    public function __construct(
        private ?int $id,
        private string $title,
        private int $position = 0,
        private ?int $equipmentModuleId = null,
    ) {
        if (trim($title) === '') {
            throw new DomainException('Work title is required.');
        }

        if ($equipmentModuleId !== null && $equipmentModuleId < 1) {
            throw new DomainException('equipment_module_id must be positive.');
        }
    }

    public static function create(
        string $title,
        int $position = 0,
        ?int $equipmentModuleId = null,
    ): self {
        return new self(null, $title, $position, $equipmentModuleId);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function equipmentModuleId(): ?int
    {
        return $this->equipmentModuleId;
    }
}
