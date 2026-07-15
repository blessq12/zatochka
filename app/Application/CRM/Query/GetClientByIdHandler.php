<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientDTO;
use App\Application\CRM\ReadPort\ClientReadPort;

final readonly class GetClientByIdHandler
{
    public function __construct(
        private ClientReadPort $readPort,
    ) {}

    public function handle(GetClientByIdQuery $query): ?ClientDTO
    {
        return $this->readPort->findById($query->clientId);
    }
}
