<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Aggregate\ProfileAdditional;
use App\Domain\Crm\Repository\ActorRepositoryResolver;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class CreateActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ProfileAdditionalRepository $profiles,
        private ActorResponseAssembler $assembler,
    ) {}

    public function handle(
        ActorType $type,
        ?string $email = null,
        ?string $name = null,
        ?string $phone = null,
        ?string $birthday = null,
        ?string $deliveryAddress = null,
    ): ActorResponse {
        return DB::transaction(function () use ($type, $email, $name, $phone, $birthday, $deliveryAddress): ActorResponse {
            $birthdayDate = $birthday !== null && $birthday !== ''
                ? new DateTimeImmutable($birthday)
                : null;

            $profile = $this->profiles->save(
                ProfileAdditional::create($email, $name, $phone, $birthdayDate, $deliveryAddress)
            );

            $actor = $this->actors->createActor($type, (int) $profile->id());
            $actor = $this->actors->for($type)->save($actor);

            return $this->assembler->assemble($type, $actor);
        });
    }
}
