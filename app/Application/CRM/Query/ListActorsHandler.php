<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\DTO\ActorResponse;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ActorRepositoryResolver;
use App\Domain\Crm\Repository\ClientRepository;

final readonly class ListActorsHandler
{
    public function __construct(
        private ActorRepositoryResolver $actors,
        private ClientRepository $clients,
        private ActorResponseAssembler $assembler,
    ) {}

    /**
     * @return list<ActorResponse>
     */
    public function handle(
        ActorType $type,
        ?string $query = null,
        bool $recent = false,
        int $recentLimit = 8,
    ): array {
        if ($type === ActorType::Client) {
            if ($query !== null && trim($query) !== '') {
                $list = $this->clients->searchByNameOrPhone($query);
            } elseif ($recent) {
                $list = $this->clients->recent($recentLimit);
            } else {
                $list = $this->clients->all();
            }

            return array_map(
                fn ($actor): ActorResponse => $this->assembler->assemble($type, $actor),
                $list,
            );
        }

        $items = [];
        foreach ($this->actors->for($type)->all() as $actor) {
            $items[] = $this->assembler->assemble($type, $actor);
        }

        return $items;
    }
}
