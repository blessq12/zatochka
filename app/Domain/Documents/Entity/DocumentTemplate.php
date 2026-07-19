<?php

namespace App\Domain\Documents\Entity;

use App\Domain\Documents\VO\PdfTemplateKind;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class DocumentTemplate
{
    private function __construct(
        private readonly EntityId $id,
        private readonly PdfTemplateKind $kind,
        private string $name,
        private string $bodyHtml,
        private bool $isActive,
    ) {}

    public static function create(
        EntityId $id,
        PdfTemplateKind $kind,
        string $name,
        string $bodyHtml,
        bool $isActive = true,
    ): self {
        return self::reconstitute($id, $kind, $name, $bodyHtml, $isActive);
    }

    public static function reconstitute(
        EntityId $id,
        PdfTemplateKind $kind,
        string $name,
        string $bodyHtml,
        bool $isActive,
    ): self {
        return new self(
            $id,
            $kind,
            self::requireText($name, 'Name'),
            self::requireText($bodyHtml, 'Body'),
            $isActive,
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function kind(): PdfTemplateKind
    {
        return $this->kind;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function bodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function rename(string $name): void
    {
        $this->name = self::requireText($name, 'Name');
    }

    public function updateBody(string $bodyHtml): void
    {
        $this->bodyHtml = self::requireText($bodyHtml, 'Body');
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
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
