<?php

namespace App\Domain\Finance\Repository;

use App\Domain\Finance\Aggregate\EarningsGoal;

interface EarningsGoalRepository
{
    public function save(EarningsGoal $goal): EarningsGoal;

    public function findById(int $id): ?EarningsGoal;

    /**
     * @return list<EarningsGoal>
     */
    public function all(): array;
}
