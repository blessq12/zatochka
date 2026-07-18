<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class FaqItem
{
    /** @param list<string> $answerLines */
    private function __construct(
        private readonly EntityId $id,
        private string $question,
        private array $answerLines,
        private int $sortOrder,
    ) {}

    /** @param list<string> $answerLines */
    public static function create(
        EntityId $id,
        string $question,
        array $answerLines,
        int $sortOrder,
    ): self {
        return self::reconstitute($id, $question, $answerLines, $sortOrder);
    }

    /** @param list<string> $answerLines */
    public static function reconstitute(
        EntityId $id,
        string $question,
        array $answerLines,
        int $sortOrder,
    ): self {
        $lines = [];

        foreach ($answerLines as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed !== '') {
                $lines[] = $trimmed;
            }
        }

        if ($lines === []) {
            throw new DomainException('FAQ answer lines are required.');
        }

        return new self(
            $id,
            self::requireText($question, 'FAQ question'),
            $lines,
            $sortOrder,
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function question(): string
    {
        return $this->question;
    }

    /** @return list<string> */
    public function answerLines(): array
    {
        return $this->answerLines;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    private static function requireText(string $value, string $label): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new DomainException(sprintf('%s is required.', $label));
        }

        return $trimmed;
    }
}
