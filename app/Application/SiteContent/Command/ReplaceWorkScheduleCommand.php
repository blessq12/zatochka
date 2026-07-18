<?php

namespace App\Application\SiteContent\Command;

final readonly class ReplaceWorkScheduleCommand
{
    /**
     * @param list<array{
     *     id?: ?int,
     *     name: string,
     *     is_day_off: bool,
     *     day_off_text?: ?string,
     *     workshop?: ?string,
     *     delivery?: ?string
     * }> $days
     */
    public function __construct(
        public array $days,
    ) {}
}
