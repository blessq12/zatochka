<?php

namespace App\Domain\Identity\Aggregate;

final class Identity
{
    public function __construct(
        private ?int $id,
        private string $email,
        private string $passwordHash,
    ) {}

    public static function create(string $email, string $passwordHash): self
    {
        return new self(null, $email, $passwordHash);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }
}
