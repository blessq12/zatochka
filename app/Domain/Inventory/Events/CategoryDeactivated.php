<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;

class CategoryDeactivated extends DomainEvent
{
    public function __construct(
        public readonly int $categoryId
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
            'category_id' => $this->categoryId,
        ];
    }
}
