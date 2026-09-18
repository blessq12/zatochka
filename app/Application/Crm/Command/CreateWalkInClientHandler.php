<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Shared\Domain\DomainException;

final readonly class CreateWalkInClientHandler
{
    public function __construct(
        private CreateActorHandler $createActor,
    ) {}

    public function handle(string $name, string $phone): ActorResponse
    {
        $name = trim($name);
        $phone = trim($phone);

        if ($name === '' || $phone === '') {
            throw new DomainException('Name and phone are required.');
        }

        return $this->createActor->handle(
            ActorType::Client,
            email: null,
            name: $name,
            phone: $phone,
        );
    }
}
