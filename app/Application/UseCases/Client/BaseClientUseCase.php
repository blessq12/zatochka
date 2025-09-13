<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\Repository\ClientRepository;

abstract class BaseClientUseCase implements ClientUseCaseInterface
{
    protected array $data;
    protected ClientRepository $clientRepository;

    public function __construct()
    {
        $this->clientRepository = app(ClientRepository::class);
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract public function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
