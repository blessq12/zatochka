<?php

namespace App\Application\Finance\Assembler;

use App\Application\Finance\DTO\EarningsGoalResponse;
use App\Domain\Finance\Aggregate\EarningsGoal;
use App\Domain\Finance\CashEntryType;
use App\Domain\Finance\Repository\CashEntryRepository;

final readonly class EarningsGoalResponseAssembler
{
    public function __construct(
        private CashEntryRepository $cashEntries,
    ) {}

    public function assemble(EarningsGoal $goal, bool $withProgress = false): EarningsGoalResponse
    {
        $progress = null;
        if ($withProgress) {
            $summary = $this->cashEntries->summarize($goal->startsAt(), $goal->endsAt());
            $target = (float) $goal->targetAmount();
            $net = (float) $summary['net'];
            $percent = $target > 0 ? round(($net / $target) * 100, 1) : 0.0;

            $progress = [
                'income' => $summary['income'],
                'expense' => $summary['expense'],
                'net' => $summary['net'],
                'percent' => $percent,
                'series' => $this->buildSeries($goal),
            ];
        }

        return new EarningsGoalResponse(
            (int) $goal->id(),
            $goal->title(),
            $goal->targetAmount(),
            $goal->startsAt()->format('Y-m-d'),
            $goal->endsAt()->format('Y-m-d'),
            $goal->status()->value,
            $progress,
        );
    }

    /**
     * @return list<array{date: string, net: string}>
     */
    private function buildSeries(EarningsGoal $goal): array
    {
        $entries = $this->cashEntries->list($goal->startsAt(), $goal->endsAt());
        $byDay = [];
        foreach ($entries as $entry) {
            $day = $entry->occurredAt()->format('Y-m-d');
            if (! isset($byDay[$day])) {
                $byDay[$day] = 0.0;
            }
            $amount = (float) $entry->amount();
            $byDay[$day] += $entry->type() === CashEntryType::Income ? $amount : -$amount;
        }
        ksort($byDay);

        $series = [];
        $running = 0.0;
        foreach ($byDay as $day => $delta) {
            $running += $delta;
            $series[] = [
                'date' => $day,
                'net' => number_format($running, 2, '.', ''),
            ];
        }

        return $series;
    }
}
