<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;

class CategoryActivated extends DomainEvent
{
    public function __construct(
        public readonly int $categoryId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CategoryActivated';
    }

    public function eventData(): array
    {
        return [
            'category_id' => $this->categoryId,
        ];
    }
}
