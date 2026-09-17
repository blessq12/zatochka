<?php

namespace App\Application\Finance\Query;

use App\Application\Finance\Assembler\EarningsGoalResponseAssembler;
use App\Domain\Finance\Repository\EarningsGoalRepository;

final readonly class ListEarningsGoalsHandler
{
    public function __construct(
        private EarningsGoalRepository $goals,
        private EarningsGoalResponseAssembler $assembler,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        $result = [];
        foreach ($this->goals->all() as $goal) {
            $result[] = $this->assembler->assemble($goal, withProgress: true)->toArray();
        }

        return $result;
    }
}
