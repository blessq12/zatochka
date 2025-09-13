<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\UpdateClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Exception\ClientPhoneAlreadyExistsException;

class UpdateClientUseCase extends BaseClientUseCase
{
    private ?UpdateClientDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = UpdateClientDTO::fromArray($this->data);

        // Проверяем существование клиента
        $client = $this->clientRepository->get($this->dto->id);
        if (!$client) {
            throw ClientNotFoundException::forId($this->dto->id);
        }

        // Проверяем уникальность телефона если он изменяется
        if ($this->dto->phone && $this->dto->phone !== $client->getPhone()) {
            if ($this->clientRepository->existsByPhone($this->dto->phone)) {
                throw ClientPhoneAlreadyExistsException::forPhone($this->dto->phone);
            }
        }

        return $this;
    }

    public function execute(): mixed
    {
        $client = $this->clientRepository->get($this->dto->id);
        return $this->clientRepository->update($client, $this->dto->toArray());
    }
}
