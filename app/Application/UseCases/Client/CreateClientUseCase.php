<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\CreateClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Entity\Client as ClientEntity;
use App\Domain\Client\Mapper\ClientMapper;
use App\Domain\Client\AggregateRoot\ClientAggregateRoot;

class CreateClientUseCase extends BaseClientUseCase
{
    private ?CreateClientDTO $dto = null;

    public function __construct(
        private ClientMapper $clientMapper
    ) {
        parent::__construct();
    }

    public function validateSpecificData(): self
    {
        $this->dto = CreateClientDTO::fromArray($this->data);

        // Проверяем уникальность телефона
        if ($this->clientRepository->existsByPhone($this->dto->phone)) {
            throw ClientException::forPhone($this->dto->phone);
        }

        return $this;
    }

    public function execute(): ClientEntity
    {
        $client = $this->clientRepository->create($this->dto->toArray());

        $aggregate = ClientAggregateRoot::create();
        $aggregate->createClient($client->getId());
        $aggregate->persist();

        return $client;
    }
}
