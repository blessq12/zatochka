<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class RedemptionPreference
{
    public const AUTO = 'auto';
    public const SAVE = 'save';
    public const MANUAL = 'manual';

    private string $mode;

    private function __construct(string $mode)
    {
        if (!in_array($mode, [self::AUTO, self::SAVE, self::MANUAL], true)) {
            throw new \InvalidArgumentException('Invalid redemption preference: ' . $mode);
        }
        $this->mode = $mode;
    }

    public static function auto(): self
    {
        return new self(self::AUTO);
    }
    public static function save(): self
    {
        return new self(self::SAVE);
    }
    public static function manual(): self
    {
        return new self(self::MANUAL);
    }

    public static function fromString(string $mode): self
    {
        return new self($mode);
    }

    public function isAuto(): bool
    {
        return $this->mode === self::AUTO;
    }
    public function isSave(): bool
    {
        return $this->mode === self::SAVE;
    }
    public function isManual(): bool
    {
        return $this->mode === self::MANUAL;
    }
    public function toString(): string
    {
        return $this->mode;
    }
}
