<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;

final class WorkEntry
{
    public function __construct(
        private ?int $id,
        private string $title,
        private int $position = 0,
    ) {
        if (trim($title) === '') {
            throw new DomainException('Work title is required.');
        }
    }

    public static function create(string $title, int $position = 0): self
    {
        return new self(null, $title, $position);
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
}
