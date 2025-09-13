<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\DeleteClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Exception\ClientNotFoundException;

class DeleteClientUseCase extends BaseClientUseCase
{
    private ?DeleteClientDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = DeleteClientDTO::fromArray($this->data);

        // Проверяем существование клиента
        $client = $this->clientRepository->get($this->dto->id);
        if (!$client) {
            throw ClientNotFoundException::forId($this->dto->id);
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->clientRepository->delete($this->dto->id);
    }
}
