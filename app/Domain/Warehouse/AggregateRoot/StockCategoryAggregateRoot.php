<?php

namespace App\Domain\Warehouse\AggregateRoot;

use App\Domain\Warehouse\Event\StockCategoryCreated;
use App\Domain\Warehouse\Event\StockCategoryUpdated;
use App\Domain\Warehouse\Event\StockCategoryActivated;
use App\Domain\Warehouse\Event\StockCategoryDeactivated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class StockCategoryAggregateRoot extends AggregateRoot
{
    public function createStockCategory(
        int $categoryId,
        string $name,
        ?string $description,
        string $color,
        int $sortOrder,
        int $createdBy
    ): self {
        $this->recordThat(new StockCategoryCreated(
            categoryId: $categoryId,
            name: $name,
            description: $description,
            color: $color,
            sortOrder: $sortOrder,
            createdBy: $createdBy
        ));

        return $this;
    }

    public function updateStockCategory(
        int $categoryId,
        string $name,
        ?string $description,
        string $color,
        int $sortOrder,
        int $updatedBy
    ): self {
        $this->recordThat(new StockCategoryUpdated(
            categoryId: $categoryId,
            name: $name,
            description: $description,
            color: $color,
            sortOrder: $sortOrder,
            updatedBy: $updatedBy
        ));

        return $this;
    }

    public function activateStockCategory(int $categoryId, int $activatedBy): self
    {
        $this->recordThat(new StockCategoryActivated(
            categoryId: $categoryId,
            activatedBy: $activatedBy
        ));

        return $this;
    }

    public function deactivateStockCategory(int $categoryId, int $deactivatedBy): self
    {
        $this->recordThat(new StockCategoryDeactivated(
            categoryId: $categoryId,
            deactivatedBy: $deactivatedBy
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
