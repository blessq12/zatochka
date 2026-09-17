<?php

namespace App\Application\Finance\Command;

use App\Application\Finance\Assembler\EarningsGoalResponseAssembler;
use App\Application\Finance\DTO\EarningsGoalResponse;
use App\Domain\Finance\Repository\EarningsGoalRepository;
use App\Shared\Domain\DomainException;

final readonly class CancelEarningsGoalHandler
{
    public function __construct(
        private EarningsGoalRepository $goals,
        private EarningsGoalResponseAssembler $assembler,
    ) {}

    public function handle(int $id): EarningsGoalResponse
    {
        $goal = $this->goals->findById($id);
        if ($goal === null) {
            throw new DomainException('Earnings goal not found.');
        }
        $goal->cancel();

        return $this->assembler->assemble($this->goals->save($goal), withProgress: true);
    }
}
