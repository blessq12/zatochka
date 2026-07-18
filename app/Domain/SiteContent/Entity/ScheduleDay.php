<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class ScheduleDay
{
    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private bool $isDayOff,
        private ?string $dayOffText,
        private ?string $workshop,
        private ?string $delivery,
        private int $sortOrder,
    ) {}

    public static function workingDay(
        EntityId $id,
        string $name,
        string $workshop,
        string $delivery,
        int $sortOrder,
    ): self {
        return self::reconstitute(
            $id,
            $name,
            false,
            null,
            $workshop,
            $delivery,
            $sortOrder,
        );
    }

    public static function dayOff(
        EntityId $id,
        string $name,
        string $dayOffText,
        int $sortOrder,
    ): self {
        return self::reconstitute(
            $id,
            $name,
            true,
            $dayOffText,
            null,
            null,
            $sortOrder,
        );
    }

    public static function reconstitute(
        EntityId $id,
        string $name,
        bool $isDayOff,
        ?string $dayOffText,
        ?string $workshop,
        ?string $delivery,
        int $sortOrder,
    ): self {
        $normalizedName = self::requireText($name, 'Day name');

        if ($isDayOff) {
            return new self(
                $id,
                $normalizedName,
                true,
                self::requireText((string) $dayOffText, 'Day off text'),
                null,
                null,
                $sortOrder,
            );
        }

        return new self(
            $id,
            $normalizedName,
            false,
            null,
            self::requireText((string) $workshop, 'Workshop hours'),
            self::requireText((string) $delivery, 'Delivery hours'),
            $sortOrder,
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isDayOff(): bool
    {
        return $this->isDayOff;
    }

    public function dayOffText(): ?string
    {
        return $this->dayOffText;
    }

    public function workshop(): ?string
    {
        return $this->workshop;
    }

    public function delivery(): ?string
    {
        return $this->delivery;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    private static function requireText(string $value, string $label): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new DomainException(sprintf('%s is required.', $label));
        }

        return $trimmed;
    }
}
