<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\ValueObject\EntityId;

final class DeliveryInfo
{
    public const SINGLETON_ID = 1;

    /**
     * @param  list<string>  $freeConditions
     * @param  list<DeliveryAdvantage>  $advantages
     */
    private function __construct(
        private readonly EntityId $id,
        private array $freeConditions,
        private array $advantages,
    ) {}

    /**
     * @param  list<string>  $freeConditions
     * @param  list<DeliveryAdvantage>  $advantages
     */
    public static function create(array $freeConditions = [], array $advantages = []): self
    {
        return self::reconstitute(
            new EntityId(self::SINGLETON_ID),
            $freeConditions,
            $advantages,
        );
    }

    /**
     * @param  list<string>  $freeConditions
     * @param  list<DeliveryAdvantage>  $advantages
     */
    public static function reconstitute(
        EntityId $id,
        array $freeConditions,
        array $advantages,
    ): self {
        return new self(
            $id,
            self::normalizeConditions($freeConditions),
            array_values($advantages),
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    /** @return list<string> */
    public function freeConditions(): array
    {
        return $this->freeConditions;
    }

    /** @return list<DeliveryAdvantage> */
    public function advantages(): array
    {
        return $this->advantages;
    }

    /**
     * @param  list<string>  $freeConditions
     * @param  list<DeliveryAdvantage>  $advantages
     */
    public function update(array $freeConditions, array $advantages): void
    {
        $this->freeConditions = self::normalizeConditions($freeConditions);
        $this->advantages = array_values($advantages);
    }

    /** @param list<string> $conditions */
    private static function normalizeConditions(array $conditions): array
    {
        $normalized = [];

        foreach ($conditions as $condition) {
            $trimmed = trim((string) $condition);
            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return $normalized;
    }
}
