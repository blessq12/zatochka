<?php

namespace App\Application\Workshop\Command;

final readonly class CompleteDiagnosisCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $diagnosisId,
        public string $summary,
        public ?string $technicalNotes = null,
    ) {}
}
