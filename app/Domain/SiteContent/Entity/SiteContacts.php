<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class SiteContacts
{
    public const SINGLETON_ID = 1;

    /**
     * @param  list<string>  $addressDetails
     * @param  list<SocialLink>  $socialLinks
     */
    private function __construct(
        private readonly EntityId $id,
        private string $contactPerson,
        private string $phone,
        private string $phoneTel,
        private string $email,
        private string $addressMain,
        private array $addressDetails,
        private array $socialLinks,
    ) {}

    /**
     * @param  list<string>  $addressDetails
     * @param  list<SocialLink>  $socialLinks
     */
    public static function create(
        string $contactPerson,
        string $phone,
        string $phoneTel,
        string $email,
        string $addressMain,
        array $addressDetails = [],
        array $socialLinks = [],
    ): self {
        return self::reconstitute(
            new EntityId(self::SINGLETON_ID),
            $contactPerson,
            $phone,
            $phoneTel,
            $email,
            $addressMain,
            $addressDetails,
            $socialLinks,
        );
    }

    /**
     * @param  list<string>  $addressDetails
     * @param  list<SocialLink>  $socialLinks
     */
    public static function reconstitute(
        EntityId $id,
        string $contactPerson,
        string $phone,
        string $phoneTel,
        string $email,
        string $addressMain,
        array $addressDetails,
        array $socialLinks,
    ): self {
        return new self(
            $id,
            self::requireText($contactPerson, 'Contact person'),
            self::requireText($phone, 'Phone'),
            self::requireText($phoneTel, 'Phone tel'),
            self::requireText($email, 'Email'),
            self::requireText($addressMain, 'Address'),
            self::normalizeDetails($addressDetails),
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

    public function phoneTel(): string
    {
        return $this->phoneTel;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function addressMain(): string
    {
        return $this->addressMain;
    }

    /** @return list<string> */
    public function addressDetails(): array
    {
        return $this->addressDetails;
    }

    /** @return list<SocialLink> */
    public function socialLinks(): array
    {
        return $this->socialLinks;
    }

    /**
     * @param  list<string>  $addressDetails
     * @param  list<SocialLink>  $socialLinks
     */
    public function update(
        string $contactPerson,
        string $phone,
        string $phoneTel,
        string $email,
        string $addressMain,
        array $addressDetails,
        array $socialLinks,
    ): void {
        $this->contactPerson = self::requireText($contactPerson, 'Contact person');
        $this->phone = self::requireText($phone, 'Phone');
        $this->phoneTel = self::requireText($phoneTel, 'Phone tel');
        $this->email = self::requireText($email, 'Email');
        $this->addressMain = self::requireText($addressMain, 'Address');
        $this->addressDetails = self::normalizeDetails($addressDetails);
        $this->socialLinks = array_values($socialLinks);
    }

    /** @param list<string> $details */
    private static function normalizeDetails(array $details): array
    {
        $normalized = [];

        foreach ($details as $detail) {
            $trimmed = trim((string) $detail);
            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return $normalized;
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
