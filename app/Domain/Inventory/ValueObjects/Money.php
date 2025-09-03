<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class Money
{
    private float $amount;
    private string $currency;

    private function __construct(float $amount, string $currency = 'RUB')
    {
        $this->ensureValidAmount($amount);
        $this->ensureValidCurrency($currency);
        $this->amount = $amount;
        $this->currency = $currency;
    }

    private function ensureValidAmount(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        if ($amount > 999999999.99) {
            throw new InvalidArgumentException('Amount is too large');
        }
    }

    private function ensureValidCurrency(string $currency): void
    {
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be 3 characters');
        }

        if (!ctype_alpha($currency)) {
            throw new InvalidArgumentException('Currency must contain only letters');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'RUB'): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = 'RUB'): self
    {
        return new self(0.0, $currency);
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies');
        }

        $newAmount = $this->amount - $other->amount;
        if ($newAmount < 0) {
            throw new InvalidArgumentException('Result cannot be negative');
        }

        return new self($newAmount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Factor cannot be negative');
        }

        return new self($this->amount * $factor, $this->currency);
    }

    public function divide(float $divisor): self
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException('Divisor must be positive');
        }

        return new self($this->amount / $divisor, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    public function isGreaterThan(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare money with different currencies');
        }

        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare money with different currencies');
        }

        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            return false;
        }

        return abs($this->amount - $other->amount) < 0.01; // Точность до копеек
    }

    public function format(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
