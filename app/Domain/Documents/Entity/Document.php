<?php

namespace App\Domain\Documents\Entity;

use App\Domain\Documents\VO\DocumentType;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class Document
{
    private function __construct(
        private readonly DocumentType $type,
        private string $title,
        private string $bodyHtml,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        DocumentType $type,
        string $title,
        string $bodyHtml,
        ?DateTimeImmutable $updatedAt = null,
    ): self {
        return self::reconstitute(
            $type,
            $title,
            $bodyHtml,
            $updatedAt ?? new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        DocumentType $type,
        string $title,
        string $bodyHtml,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $type,
            self::requireText($title, 'Title'),
            self::requireText($bodyHtml, 'Body'),
            $updatedAt,
        );
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function bodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateContent(string $title, string $bodyHtml): void
    {
        $this->title = self::requireText($title, 'Title');
        $this->bodyHtml = self::requireText($bodyHtml, 'Body');
        $this->updatedAt = new DateTimeImmutable();
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
