<?php

namespace App\Infrastructure\Catalog\Persistence\Mapper;

use App\Domain\Catalog\Entity\Branch;
use App\Infrastructure\Catalog\Persistence\Eloquent\BranchModel;

final class BranchMapper
{
    public function toDomain(BranchModel $model): Branch
    {
        return new Branch(
            id: $model->id,
            name: $model->name,
            address: $model->address,
            phone: $model->phone,
            isActive: $model->is_active,
        );
    }

    public function fillModel(Branch $branch, BranchModel $model): void
    {
        $model->fill([
            'name' => $branch->name(),
            'address' => $branch->address(),
            'phone' => $branch->phone(),
            'is_active' => $branch->isActive(),
        ]);
    }
}
