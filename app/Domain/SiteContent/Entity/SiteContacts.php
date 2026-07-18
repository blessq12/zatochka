<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class SiteContacts
{
    public const SINGLETON_ID = 1;

    /**
     * @param  list<SocialLink>  $socialLinks
     */
    private function __construct(
        private readonly EntityId $id,
        private string $contactPerson,
        private string $phone,
        private string $email,
        private string $addressMain,
        private string $entranceDirections,
        private array $socialLinks,
    ) {}

    /**
     * @param  list<SocialLink>  $socialLinks
     */
    public static function create(
        string $contactPerson,
        string $phone,
        string $email,
        string $addressMain,
        string $entranceDirections = '',
        array $socialLinks = [],
    ): self {
        return self::reconstitute(
            new EntityId(self::SINGLETON_ID),
            $contactPerson,
            $phone,
            $email,
            $addressMain,
            $entranceDirections,
            $socialLinks,
        );
    }

    /**
     * @param  list<SocialLink>  $socialLinks
     */
    public static function reconstitute(
        EntityId $id,
        string $contactPerson,
        string $phone,
        string $email,
        string $addressMain,
        string $entranceDirections,
        array $socialLinks,
    ): self {
        return new self(
            $id,
            self::requireText($contactPerson, 'Contact person'),
            self::requireText($phone, 'Phone'),
            self::requireText($email, 'Email'),
            self::requireText($addressMain, 'Address'),
            self::normalizeDirections($entranceDirections),
            array_values($socialLinks),
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function contactPerson(): string
    {
        return $this->contactPerson;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function addressMain(): string
    {
        return $this->addressMain;
    }

    public function entranceDirections(): string
    {
        return $this->entranceDirections;
    }

    /** @return list<SocialLink> */
    public function socialLinks(): array
    {
        return $this->socialLinks;
    }

    /**
     * @param  list<SocialLink>  $socialLinks
     */
    public function update(
        string $contactPerson,
        string $phone,
        string $email,
        string $addressMain,
        string $entranceDirections,
        array $socialLinks,
    ): void {
        $this->contactPerson = self::requireText($contactPerson, 'Contact person');
        $this->phone = self::requireText($phone, 'Phone');
        $this->email = self::requireText($email, 'Email');
        $this->addressMain = self::requireText($addressMain, 'Address');
        $this->entranceDirections = self::normalizeDirections($entranceDirections);
        $this->socialLinks = array_values($socialLinks);
    }

    private static function normalizeDirections(string $directions): string
    {
        return trim($directions);
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
