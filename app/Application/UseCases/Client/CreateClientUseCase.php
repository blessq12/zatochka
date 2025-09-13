<?php

namespace App\Application\UseCases\Client;

use App\Domain\Client\DTO\CreateClientDTO;
use App\Domain\Client\Exception\ClientException;
use App\Domain\Client\Exception\ClientPhoneAlreadyExistsException;
use App\Domain\Client\AggregateRoot\ClientAggregateRoot;
use App\Domain\Client\Entity\Client as ClientEntity;
use App\Domain\Client\Mapper\ClientMapper;
use Illuminate\Support\Str;

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
            throw ClientPhoneAlreadyExistsException::forPhone($this->dto->phone);
        }

        return $this;
    }

    public function execute(): ClientEntity
    {
        // Генерируем UUID для клиента
        $clientId = Str::uuid()->toString();

        // Создаем Aggregate Root
        $aggregate = ClientAggregateRoot::retrieve($clientId);

        // Вызываем метод создания клиента через Aggregate Root
        $aggregate->createClient(
            clientId: $clientId,
            phone: $this->dto->phone,
            fullName: $this->dto->fullName,
            clientData: $this->dto->toArray()
        );

        // Сохраняем события в Event Store
        $aggregate->persist();

        // Возвращаем Domain Entity (будет создана через Projector)
        return new ClientEntity(
            id: $clientId,
            fullName: $this->dto->fullName,
            phone: $this->dto->phone,
            telegram: $this->dto->telegram,
            birthDate: $this->dto->birthDate,
            deliveryAddress: $this->dto->deliveryAddress,
            isDeleted: false,
            createdAt: now(),
            updatedAt: now()
        );
    }
}
