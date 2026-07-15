<?php

namespace App\Application\CRM\ReadPort;

use App\Application\CRM\DTO\ClientDTO;

interface ClientReadPort
{
    public function findById(int $clientId): ?ClientDTO;

    public function findByPhone(string $phone): ?ClientDTO;
}
