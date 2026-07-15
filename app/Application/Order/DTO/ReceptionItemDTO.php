<?php

namespace App\Application\Order\DTO;

final readonly class ReceptionItemDTO
{
    /** @param list<string> $attachmentRefs */
    public function __construct(
        public int $orderItemId,
        public int $receptionId,
        public string $conditionDescription,
        public ?string $visualNotes = null,
        public array $attachmentRefs = [],
    ) {}
}
