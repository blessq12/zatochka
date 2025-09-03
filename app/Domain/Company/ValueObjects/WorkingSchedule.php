<?php

namespace App\Domain\Company\ValueObjects;

use InvalidArgumentException;

class WorkingSchedule
{
    private array $schedule;

    private const DAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday',
        'friday', 'saturday', 'sunday'
    ];

    private function __construct(array $schedule)
    {
        $this->ensureValidSchedule($schedule);
        $this->schedule = $schedule;
    }

    private function ensureValidSchedule(array $schedule): void
    {
        foreach (self::DAYS as $day) {
            if (!isset($schedule[$day])) {
                throw new InvalidArgumentException("Missing schedule for day: {$day}");
            }

            $daySchedule = $schedule[$day];

            if (!is_array($daySchedule)) {
                throw new InvalidArgumentException("Invalid schedule format for day: {$day}");
            }

            if (!isset($daySchedule['is_working'])) {
                throw new InvalidArgumentException("Missing 'is_working' flag for day: {$day}");
            }

            if (!is_bool($daySchedule['is_working'])) {
                throw new InvalidArgumentException("'is_working' must be boolean for day: {$day}");
            }

            if ($daySchedule['is_working']) {
                if (!isset($daySchedule['start']) || !isset($daySchedule['end'])) {
                    throw new InvalidArgumentException("Working days must have start and end times for day: {$day}");
                }

                if (!$this->isValidTime($daySchedule['start'])) {
                    throw new InvalidArgumentException("Invalid start time format for day: {$day}");
                }

                if (!$this->isValidTime($daySchedule['end'])) {
                    throw new InvalidArgumentException("Invalid end time format for day: {$day}");
                }

                if (!$this->isValidTimeRange($daySchedule['start'], $daySchedule['end'])) {
                    throw new InvalidArgumentException("End time must be after start time for day: {$day}");
                }
            }
        }
    }

    private function isValidTime(string $time): bool
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    private function isValidTimeRange(string $start, string $end): bool
    {
        $startMinutes = $this->timeToMinutes($start);
        $endMinutes = $this->timeToMinutes($end);

        return $endMinutes > $startMinutes;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    public static function fromArray(array $schedule): self
    {
        return new self($schedule);
    }

    public static function createDefault(): self
    {
        $defaultSchedule = [];
        foreach (self::DAYS as $day) {
            $defaultSchedule[$day] = [
                'is_working' => false,
                'start' => null,
                'end' => null,
                'note' => 'Выходной'
            ];
        }

        return new self($defaultSchedule);
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function getDaySchedule(string $day): ?array
    {
        $day = strtolower($day);
        return $this->schedule[$day] ?? null;
    }

    public function isWorkingDay(string $day): bool
    {
        $day = strtolower($day);
        return $this->schedule[$day]['is_working'] ?? false;
    }

    public function isWorkingToday(): bool
    {
        $today = strtolower(now()->format('l'));
        return $this->isWorkingDay($today);
    }

    public function isWorkingNow(): bool
    {
        if (!$this->isWorkingToday()) {
            return false;
        }

        $now = now();
        $today = strtolower($now->format('l'));
        $daySchedule = $this->schedule[$today];

        $currentTime = $now->format('H:i');
        return $currentTime >= $daySchedule['start'] && $currentTime <= $daySchedule['end'];
    }

    public function getWorkingDays(): array
    {
        $workingDays = [];
        foreach ($this->schedule as $day => $schedule) {
            if ($schedule['is_working']) {
                $workingDays[] = $day;
            }
        }
        return $workingDays;
    }

    public function getNextWorkingDay(): ?string
    {
        $today = now();

        for ($i = 1; $i <= 7; $i++) {
            $nextDay = $today->copy()->addDays($i);
            $dayName = strtolower($nextDay->format('l'));

            if ($this->isWorkingDay($dayName)) {
                return $dayName;
            }
        }

        return null;
    }

    public function updateDaySchedule(string $day, array $daySchedule): void
    {
        $day = strtolower($day);
        if (!in_array($day, self::DAYS)) {
            throw new InvalidArgumentException("Invalid day: {$day}");
        }

        $tempSchedule = $this->schedule;
        $tempSchedule[$day] = $daySchedule;

        $this->ensureValidSchedule($tempSchedule);
        $this->schedule = $tempSchedule;
    }

    public function equals(WorkingSchedule $other): bool
    {
        return $this->schedule === $other->schedule;
    }

    public function toArray(): array
    {
        return $this->schedule;
    }
}
