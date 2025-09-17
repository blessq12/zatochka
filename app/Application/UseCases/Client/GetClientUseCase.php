<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\GetClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Exception\ClientNotFoundException;

class GetClientUseCase extends BaseClientUseCase
{
    private ?GetClientDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = GetClientDTO::fromArray($this->data);

        $client = $this->clientRepository->get($this->dto->id);
        if (!$client) {
            throw ClientNotFoundException::forId($this->dto->id);
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->clientRepository->get($this->dto->id);
    }
}
