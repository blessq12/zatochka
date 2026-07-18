<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\SiteContacts;
use App\Domain\SiteContent\Entity\SocialLink;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use App\Shared\ValueObject\EntityId;

final class SiteContactsMapper
{
    public function toDomain(SiteContactsModel $model): SiteContacts
    {
        $links = [];

        foreach ((array) $model->social_links as $link) {
            $links[] = new SocialLink(
                (string) ($link['name'] ?? ''),
                (string) ($link['url'] ?? ''),
                isset($link['icon']) && $link['icon'] !== '' ? (string) $link['icon'] : null,
            );
        }

        return SiteContacts::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->contact_person,
            (string) $model->phone,
            (string) $model->email,
            (string) $model->address_main,
            (string) $model->entrance_directions,
            $links,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(SiteContacts $contacts): array
    {
        return [
            'id' => $contacts->id()->value,
            'contact_person' => $contacts->contactPerson(),
            'phone' => $contacts->phone(),
            'email' => $contacts->email(),
            'address_main' => $contacts->addressMain(),
            'entrance_directions' => $contacts->entranceDirections(),
            'social_links' => array_map(
                static fn (SocialLink $link): array => $link->toArray(),
                $contacts->socialLinks(),
            ),
        ];
    }
}
