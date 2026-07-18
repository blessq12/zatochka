<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\SiteContacts;

interface SiteContactsRepository
{
    public function get(): SiteContacts;

    public function save(SiteContacts $contacts): void;
}
