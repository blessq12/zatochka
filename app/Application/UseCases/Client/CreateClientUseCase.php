<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\CreateClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Exception\ClientPhoneAlreadyExistsException;

class CreateClientUseCase extends BaseClientUseCase
{
    private ?CreateClientDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = CreateClientDTO::fromArray($this->data);

        // Проверяем уникальность телефона
        if ($this->clientRepository->existsByPhone($this->dto->phone)) {
            throw ClientPhoneAlreadyExistsException::forPhone($this->dto->phone);
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->clientRepository->create($this->dto->toArray());
    }
}
