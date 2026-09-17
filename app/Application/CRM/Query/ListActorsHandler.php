<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;

final readonly class ListActorsHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ActorResponseAssembler $assembler,
    ) {}

    /**
     * @return list<ActorResponse>
     */
    public function handle(ActorType $type): array
    {
        $items = [];

        foreach ($this->actors->for($type)->all() as $actor) {
            $items[] = $this->assembler->assemble($type, $actor);
        }

        return $items;
    }
}
