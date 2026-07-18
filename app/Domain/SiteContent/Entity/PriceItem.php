<?php

namespace App\Domain\SiteContent\Entity;

use App\Domain\SiteContent\VO\PricePrefix;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class PriceItem
{
    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private string $price,
        private ?PricePrefix $prefix,
        private ?string $description,
        private int $sortOrder,
    ) {}

    public static function create(
        EntityId $id,
        string $name,
        string $price,
        ?PricePrefix $prefix,
        ?string $description,
        int $sortOrder,
    ): self {
        return self::reconstitute($id, $name, $price, $prefix, $description, $sortOrder);
    }

    public static function reconstitute(
        EntityId $id,
        string $name,
        string $price,
        ?PricePrefix $prefix,
        ?string $description,
        int $sortOrder,
    ): self {
        $normalizedDescription = $description === null ? null : trim($description);

        return new self(
            $id,
            self::requireText($name, 'Price item name'),
            self::requireText($price, 'Price'),
            $prefix,
            $normalizedDescription === '' ? null : $normalizedDescription,
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

    public function price(): string
    {
        return $this->price;
    }

    public function prefix(): ?PricePrefix
    {
        return $this->prefix;
    }

    public function description(): ?string
    {
        return $this->description;
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
