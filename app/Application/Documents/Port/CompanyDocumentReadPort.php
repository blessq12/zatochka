<?php

namespace App\Application\Documents\Port;

use App\Application\Documents\DTO\CompanyDocumentSnapshot;

interface CompanyDocumentReadPort
{
    public function get(): CompanyDocumentSnapshot;
}
