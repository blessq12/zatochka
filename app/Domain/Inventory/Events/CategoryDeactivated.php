<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\CategoryId;

class CategoryDeactivated extends DomainEvent
{
    public function __construct(
        private readonly CategoryId $categoryId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CategoryDeactivated';
    }

    public function eventData(): array
    {
        return [
            'category_id' => (string) $this->categoryId,
        ];
    }

    public function categoryId(): CategoryId
    {
        return $this->categoryId;
    }
}
