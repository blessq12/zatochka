<?php

namespace App\Domain\Client\Mapper;

use App\Models\Client;

interface ClientMapper
{
    public function toDomain(Client $model): \App\Domain\Client\Entity\Client;

    public function toEloquent(\App\Domain\Client\Entity\Client $entity): Client;

    public function fromArray(array $data): \App\Domain\Client\Entity\Client;
}
