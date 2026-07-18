<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Entity\SocialLink;
use App\Domain\SiteContent\Repository\SiteContactsRepository;

final readonly class UpdateSiteContactsHandler
{
    public function __construct(
        private SiteContactsRepository $contacts,
    ) {}

    public function handle(UpdateSiteContactsCommand $command): void
    {
        $links = [];

        foreach ($command->socialLinks as $link) {
            $links[] = new SocialLink(
                (string) ($link['name'] ?? ''),
                (string) ($link['url'] ?? ''),
                self::nullableString($link['icon'] ?? null),
            );
        }

        $aggregate = $this->contacts->get();
        $aggregate->update(
            $command->contactPerson,
            $command->phone,
            $command->email,
            $command->addressMain,
            $command->entranceDirections,
            $links,
        );
        $this->contacts->save($aggregate);
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
