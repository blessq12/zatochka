<?php

namespace App\Application\Order\Port;

use App\Application\Order\DTO\CompanyDocumentSnapshot;

interface CompanyDocumentReadPort
{
    public function get(): CompanyDocumentSnapshot;
}
