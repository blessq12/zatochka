<?php

namespace App\Application\Finance\DTO;

final readonly class EarningsGoalResponse
{
    /**
     * @param  array{
     *     income: string,
     *     expense: string,
     *     net: string,
     *     percent: float,
     *     series: list<array{date: string, net: string}>
     * }|null  $progress
     */
    public function __construct(
        public int $id,
        public ?string $title,
        public string $targetAmount,
        public string $startsAt,
        public string $endsAt,
        public string $status,
        public ?array $progress = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'target_amount' => $this->targetAmount,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status,
        ];
        if ($this->progress !== null) {
            $data['progress'] = $this->progress;
        }

        return $data;
    }
}
