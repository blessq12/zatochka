<?php

namespace App\Application\Finance\Command;

use App\Application\Finance\Assembler\EarningsGoalResponseAssembler;
use App\Application\Finance\DTO\EarningsGoalResponse;
use App\Domain\Finance\Aggregate\EarningsGoal;
use App\Domain\Finance\Repository\EarningsGoalRepository;
use DateTimeImmutable;

final readonly class CreateEarningsGoalHandler
{
    public function __construct(
        private EarningsGoalRepository $goals,
        private EarningsGoalResponseAssembler $assembler,
    ) {}

    public function handle(
        string $targetAmount,
        string $startsAt,
        string $endsAt,
        ?string $title = null,
    ): EarningsGoalResponse {
        $goal = EarningsGoal::create(
            $targetAmount,
            new DateTimeImmutable($startsAt),
            new DateTimeImmutable($endsAt),
            $title,
        );

        return $this->assembler->assemble($this->goals->save($goal), withProgress: true);
    }
}
