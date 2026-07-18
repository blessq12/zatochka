<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\DeliveryInfo;

interface DeliveryInfoRepository
{
    public function get(): DeliveryInfo;

    public function save(DeliveryInfo $deliveryInfo): void;
}
