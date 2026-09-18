<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Aggregate\ProfileAdditional;

interface ProfileAdditionalRepository
{
    public function save(ProfileAdditional $profile): ProfileAdditional;

    public function findById(int $id): ?ProfileAdditional;

    public function delete(ProfileAdditional $profile): void;
}
