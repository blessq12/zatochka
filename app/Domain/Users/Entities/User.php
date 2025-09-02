<?php

namespace App\Domain\Users\Entities;

use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\PasswordHash;
use App\Domain\Users\ValueObjects\UserId;
use InvalidArgumentException;

class User implements AggregateRoot
{
    private UserId $userId;
    private string $name;
    private Email $email;
    private PasswordHash $passwordHash;
    private bool $isActive;
    private bool $isDeleted;
    /** @var string[] */
    private array $roles;

    private function __construct(
        UserId $userId,
        string $name,
        Email $email,
        PasswordHash $passwordHash,
        bool $isActive,
        bool $isDeleted,
        array $roles
    ) {
        $this->assertName($name);
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->isActive = $isActive;
        $this->isDeleted = $isDeleted;
        $this->roles = array_values(array_unique($roles));
    }

    public static function register(UserId $userId, string $name, Email $email, PasswordHash $passwordHash): self
    {
        return new self($userId, $name, $email, $passwordHash, true, false, []);
    }

    public static function reconstitute(
        UserId $userId,
        string $name,
        Email $email,
        PasswordHash $passwordHash,
        bool $isActive,
        bool $isDeleted,
        array $roles
    ): self {
        return new self($userId, $name, $email, $passwordHash, $isActive, $isDeleted, $roles);
    }

    private function assertNotDeleted(): void
    {
        if ($this->isDeleted) {
            throw new InvalidArgumentException('User is deleted');
        }
    }

    private function assertName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Name must not be empty');
        }
    }

    public function activate(): void
    {
        $this->assertNotDeleted();
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->assertNotDeleted();
        $this->isActive = false;
    }

    public function markDeleted(): void
    {
        $this->isDeleted = true;
        $this->isActive = false;
    }

    public function changeEmail(Email $newEmail): void
    {
        $this->assertNotDeleted();
        $this->email = $newEmail;
    }

    public function rename(string $newName): void
    {
        $this->assertNotDeleted();
        $this->assertName($newName);
        $this->name = trim($newName);
    }

    public function setPassword(PasswordHash $newHash): void
    {
        $this->assertNotDeleted();
        $this->passwordHash = $newHash;
    }

    public function replaceRoles(array $roles): void
    {
        $this->assertNotDeleted();
        $this->roles = array_values(array_unique(array_map('strval', $roles)));
    }

    public function assignRoles(array $roles): void
    {
        $this->assertNotDeleted();
        $merged = array_merge($this->roles, array_map('strval', $roles));
        $this->roles = array_values(array_unique($merged));
    }

    public function removeRoles(array $roles): void
    {
        $this->assertNotDeleted();
        $remove = array_map('strval', $roles);
        $this->roles = array_values(array_filter(
            $this->roles,
            fn(string $r) => !in_array($r, $remove, true)
        ));
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function email(): Email
    {
        return $this->email;
    }
    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }
    /** @return string[] */
    public function roles(): array
    {
        return $this->roles;
    }
}
