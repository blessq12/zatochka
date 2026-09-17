<?php

namespace App\Application\Manager\Query;

use App\Application\Finance\Assembler\EarningsGoalResponseAssembler;
use App\Domain\Finance\EarningsGoalStatus;
use App\Domain\Finance\Repository\CashEntryRepository;
use App\Domain\Finance\Repository\EarningsGoalRepository;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;

final readonly class GetManagerDashboardHandler
{
    private const ATTENTION_STATUSES = [
        'created',
        'works_completed',
        'ready',
        'in_progress',
    ];

    private const MAX_ACTIVE_GOALS = 2;

    public function __construct(
        private OrderRepository $orders,
        private CashEntryRepository $cashEntries,
        private EarningsGoalRepository $goals,
        private EarningsGoalResponseAssembler $goalAssembler,
    ) {}

    /**
     * @return array{
     *     attention: array{
     *         created: int,
     *         works_completed: int,
     *         ready: int,
     *         in_progress: int
     *     },
     *     finance: array{balance: string},
     *     goals: list<array{
     *         id: int,
     *         title: string|null,
     *         target_amount: string,
     *         net: string,
     *         percent: float
     *     }>
     * }
     */
    public function handle(): array
    {
        $counts = $this->orders->countByStatuses(self::ATTENTION_STATUSES);
        $summary = $this->cashEntries->summarize();

        $activeGoals = [];
        foreach ($this->goals->all() as $goal) {
            if ($goal->status() !== EarningsGoalStatus::Active) {
                continue;
            }
            $payload = $this->goalAssembler->assemble($goal, withProgress: true)->toArray();
            $activeGoals[] = [
                'id' => $payload['id'],
                'title' => $payload['title'],
                'target_amount' => $payload['target_amount'],
                'net' => $payload['progress']['net'] ?? '0.00',
                'percent' => $payload['progress']['percent'] ?? 0.0,
            ];
            if (count($activeGoals) >= self::MAX_ACTIVE_GOALS) {
                break;
            }
        }

        return [
            'attention' => [
                'created' => $counts[OrderStatus::Created->value] ?? 0,
                'works_completed' => $counts[OrderStatus::WorksCompleted->value] ?? 0,
                'ready' => $counts[OrderStatus::Ready->value] ?? 0,
                'in_progress' => $counts[OrderStatus::InProgress->value] ?? 0,
            ],
            'finance' => [
                'balance' => $summary['balance'],
            ],
            'goals' => $activeGoals,
        ];
    }
}
