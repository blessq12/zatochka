<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\StockCategory;
use App\Domain\Inventory\ValueObjects\CategoryId;
use App\Domain\Inventory\ValueObjects\CategoryName;
use App\Domain\Inventory\Interfaces\StockCategoryRepositoryInterface;
use App\Domain\Shared\Events\EventBusInterface;
use InvalidArgumentException;

class StockCategoryService
{
    public function __construct(
        private readonly StockCategoryRepositoryInterface $categoryRepository,
        private readonly EventBusInterface $eventBus
    ) {}

    public function createCategory(
        CategoryId $id,
        CategoryName $name,
        ?string $description = null,
        ?string $color = null,
        int $sortOrder = 0
    ): StockCategory {
        // Проверяем уникальность названия
        if ($this->categoryRepository->existsByName((string) $name)) {
            throw new InvalidArgumentException('Category with this name already exists');
        }

        $category = StockCategory::create($id, $name, $description, $color, $sortOrder);
        $this->categoryRepository->save($category);

        // Публикуем события
        $this->publishEvents($category);

        return $category;
    }

    public function updateCategory(
        CategoryId $id,
        ?CategoryName $name = null,
        ?string $description = null,
        ?string $color = null,
        ?int $sortOrder = null
    ): StockCategory {
        $category = $this->getCategoryOrFail($id);

        if ($name !== null) {
            // Проверяем уникальность нового названия
            $existingCategory = $this->categoryRepository->findByName((string) $name);
            if ($existingCategory && !$existingCategory->id()->equals($id)) {
                throw new InvalidArgumentException('Category with this name already exists');
            }
            $category->updateName($name);
        }

        if ($description !== null) {
            $category->updateDescription($description);
        }

        if ($color !== null) {
            $category->updateColor($color);
        }

        if ($sortOrder !== null) {
            $category->updateSortOrder($sortOrder);
        }

        $this->categoryRepository->save($category);
        $this->publishEvents($category);

        return $category;
    }

    public function activateCategory(CategoryId $id): StockCategory
    {
        $category = $this->getCategoryOrFail($id);
        $category->activate();
        $this->categoryRepository->save($category);
        $this->publishEvents($category);
        return $category;
    }

    public function deactivateCategory(CategoryId $id): StockCategory
    {
        $category = $this->getCategoryOrFail($id);
        $category->deactivate();
        $this->categoryRepository->save($category);
        $this->publishEvents($category);
        return $category;
    }

    public function deleteCategory(CategoryId $id): void
    {
        $category = $this->getCategoryOrFail($id);

        if (!$category->canBeDeleted()) {
            throw new InvalidArgumentException('Category cannot be deleted');
        }

        $category->markDeleted();
        $this->categoryRepository->save($category);
        $this->publishEvents($category);
    }

    public function getCategory(CategoryId $id): ?StockCategory
    {
        return $this->categoryRepository->findById($id);
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getActiveCategories(): array
    {
        return $this->categoryRepository->findActive();
    }

    public function getCategoriesBySortOrder(): array
    {
        return $this->categoryRepository->findBySortOrder(0);
    }

    private function getCategoryOrFail(CategoryId $id): StockCategory
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new InvalidArgumentException('Category not found');
        }
        return $category;
    }

    private function publishEvents(StockCategory $category): void
    {
        while ($category->hasEvents()) {
            $events = $category->pullEvents();
            foreach ($events as $event) {
                $this->eventBus->publish($event);
            }
        }
    }
}
