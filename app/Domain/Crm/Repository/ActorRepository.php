<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Aggregate\Actor;

interface ActorRepository
{
    public function save(Actor $actor): Actor;

    public function findById(int $id): ?Actor;

    /**
     * @return list<Actor>
     */
    public function all(): array;

    public function delete(Actor $actor): void;
}
