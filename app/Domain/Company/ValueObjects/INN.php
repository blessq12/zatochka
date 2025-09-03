<?php

namespace App\Domain\Company\ValueObjects;

use InvalidArgumentException;

class INN
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidINN($value);
        $this->value = trim($value);
    }

    private function ensureValidINN(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('INN cannot be empty');
        }

        if (!preg_match('/^\d{10,12}$/', $value)) {
            throw new InvalidArgumentException('INN must be 10 or 12 digits');
        }

        // Проверка контрольной суммы для ИНН
        if (!$this->validateINNChecksum($value)) {
            throw new InvalidArgumentException('Invalid INN checksum');
        }
    }

    private function validateINNChecksum(string $inn): bool
    {
        $length = strlen($inn);

        if ($length === 10) {
            // Для ИП (10 цифр)
            $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
            $sum = 0;

            for ($i = 0; $i < 9; $i++) {
                $sum += (int)$inn[$i] * $weights[$i];
            }

            $checksum = ($sum % 11) % 10;
            return $checksum === (int)$inn[9];
        }

        if ($length === 12) {
            // Для организаций (12 цифр)
            $weights1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
            $weights2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

            $sum1 = 0;
            $sum2 = 0;

            for ($i = 0; $i < 10; $i++) {
                $sum1 += (int)$inn[$i] * $weights1[$i];
                $sum2 += (int)$inn[$i] * $weights2[$i];
            }

            $sum2 += (int)$inn[10] * 8;

            $checksum1 = ($sum1 % 11) % 10;
            $checksum2 = ($sum2 % 11) % 10;

            return $checksum1 === (int)$inn[10] && $checksum2 === (int)$inn[11];
        }

        return false;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(INN $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isIndividual(): bool
    {
        return strlen($this->value) === 12;
    }

    public function isOrganization(): bool
    {
        return strlen($this->value) === 10;
    }
}
