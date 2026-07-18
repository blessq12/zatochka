<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\FaqCatalog;

interface FaqCatalogRepository
{
    public function get(): FaqCatalog;

    public function save(FaqCatalog $catalog): void;
}
