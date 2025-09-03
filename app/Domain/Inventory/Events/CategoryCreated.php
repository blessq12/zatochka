<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\CategoryName;

class CategoryCreated extends DomainEvent
{
    public function __construct(
        private readonly int $categoryId,
        private readonly CategoryName $name,
        private readonly ?string $description,
        private readonly ?string $color,
        private readonly int $sortOrder
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CategoryCreated';
    }

    public function eventData(): array
    {
        return [
            'category_id' => $this->categoryId,
            'name' => (string) $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sortOrder,
        ];
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function name(): CategoryName
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function color(): ?string
    {
        return $this->color;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
