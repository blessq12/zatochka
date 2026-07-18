<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class CompanyProfile
{
    public const SINGLETON_ID = 1;

    private function __construct(
        private readonly EntityId $id,
        private string $ownerName,
        private string $inn,
        private string $ogrn,
        private string $legalAddress,
        private string $actualAddress,
    ) {}

    public static function create(
        string $ownerName,
        string $inn,
        string $ogrn,
        string $legalAddress,
        string $actualAddress,
    ): self {
        return self::reconstitute(
            new EntityId(self::SINGLETON_ID),
            $ownerName,
            $inn,
            $ogrn,
            $legalAddress,
            $actualAddress,
        );
    }

    public static function reconstitute(
        EntityId $id,
        string $ownerName,
        string $inn,
        string $ogrn,
        string $legalAddress,
        string $actualAddress,
    ): self {
        $profile = new self(
            $id,
            self::requireText($ownerName, 'Owner name'),
            self::requireText($inn, 'INN'),
            self::requireText($ogrn, 'OGRN'),
            self::requireText($legalAddress, 'Legal address'),
            self::requireText($actualAddress, 'Actual address'),
        );

        return $profile;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function ownerName(): string
    {
        return $this->ownerName;
    }

    public function inn(): string
    {
        return $this->inn;
    }

    public function ogrn(): string
    {
        return $this->ogrn;
    }

    public function legalAddress(): string
    {
        return $this->legalAddress;
    }

    public function actualAddress(): string
    {
        return $this->actualAddress;
    }

    public function update(
        string $ownerName,
        string $inn,
        string $ogrn,
        string $legalAddress,
        string $actualAddress,
    ): void {
        $this->ownerName = self::requireText($ownerName, 'Owner name');
        $this->inn = self::requireText($inn, 'INN');
        $this->ogrn = self::requireText($ogrn, 'OGRN');
        $this->legalAddress = self::requireText($legalAddress, 'Legal address');
        $this->actualAddress = self::requireText($actualAddress, 'Actual address');
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
