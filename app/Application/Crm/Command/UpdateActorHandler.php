<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class UpdateActorHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ProfileAdditionalRepository $profiles,
        private ActorResponseAssembler $assembler,
    ) {}

    /**
     * @param  array{
     *     name?: string|null,
     *     phone?: string|null,
     *     birthday?: string|null,
     *     delivery_address?: string|null
     * }  $attributes
     */
    public function handle(ActorType $type, int $id, array $attributes): ?ActorResponse
    {
        return DB::transaction(function () use ($type, $id, $attributes): ?ActorResponse {
            $repository = $this->actors->for($type);
            $actor = $repository->findById($id);

            if ($actor === null) {
                return null;
            }

            $profile = $this->profiles->findById($actor->profileAdditionalId());

            if ($profile === null) {
                return null;
            }

            $name = array_key_exists('name', $attributes) ? $attributes['name'] : $profile->name();
            $phone = array_key_exists('phone', $attributes) ? $attributes['phone'] : $profile->phone();
            $deliveryAddress = array_key_exists('delivery_address', $attributes)
                ? $attributes['delivery_address']
                : $profile->deliveryAddress();

            if (array_key_exists('birthday', $attributes)) {
                $birthday = $attributes['birthday'] !== null && $attributes['birthday'] !== ''
                    ? new DateTimeImmutable((string) $attributes['birthday'])
                    : null;
            } else {
                $birthday = $profile->birthday();
            }

            $profile->changeDetails($name, $phone, $birthday, $deliveryAddress);
            $this->profiles->save($profile);

            return $this->assembler->assemble($type, $actor);
        });
    }
}
