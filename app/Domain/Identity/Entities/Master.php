<?php

namespace App\Domain\Identity\Entities;

final class Master
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $surname,
        private string $email,
        private ?string $phone,
        private bool $notificationsEnabled,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function surname(): string
    {
        return $this->surname;
    }

    public function fullName(): string
    {
        return trim($this->name.' '.$this->surname);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function notificationsEnabled(): bool
    {
        return $this->notificationsEnabled;
    }
}
