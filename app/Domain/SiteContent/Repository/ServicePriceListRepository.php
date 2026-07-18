<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\ServicePriceList;

interface ServicePriceListRepository
{
    public function get(): ServicePriceList;

    public function save(ServicePriceList $priceList): void;
}
