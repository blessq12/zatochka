<?php

namespace App\Application\UseCases\Auth\Api;

use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\Mapper\ClientMapper;

abstract class BaseClientAuthUseCase
{
    protected array $data;

    // Все репозитории и мапперы для клиентской аутентификации
    protected ClientRepository $clientRepository;
    protected ClientMapper $clientMapper;

    public function __construct()
    {
        // Подтягиваем зависимости для клиентской аутентификации
        $this->clientRepository = app(ClientRepository::class);
        $this->clientMapper = app(ClientMapper::class);
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

    abstract protected function validateSpecificData(): self;
    abstract public function execute(): mixed;
}
