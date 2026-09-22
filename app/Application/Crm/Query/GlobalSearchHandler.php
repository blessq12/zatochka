<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Assembler\ActorResponseAssembler;
use App\Application\Crm\Assembler\EquipmentResponseAssembler;
use App\Domain\Crm\ActorType;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\EquipmentRepository;

final readonly class GlobalSearchHandler
{
    private const LIMIT = 10;

    public function __construct(
        private ClientRepository $clients,
        private EquipmentRepository $equipments,
        private ActorResponseAssembler $actorsAssembler,
        private EquipmentResponseAssembler $equipmentAssembler,
    ) {}

    /**
     * @return array{
     *     clients: list<array<string, mixed>>,
     *     equipments: list<array<string, mixed>>
     * }
     */
    public function handle(string $query): array
    {
        $term = trim($query);
        if (mb_strlen($term, 'UTF-8') < 2) {
            return [
                'clients' => [],
                'equipments' => [],
            ];
        }

        $clients = array_map(
            fn ($actor): array => $this->actorsAssembler->assemble(ActorType::Client, $actor)->toArray(),
            $this->clients->searchByNameOrPhone($term, self::LIMIT),
        );

        $equipments = array_map(
            fn ($equipment): array => $this->equipmentAssembler->assemble($equipment)->toArray(),
            $this->equipments->search($term, self::LIMIT),
        );

        return [
            'clients' => $clients,
            'equipments' => $equipments,
        ];
    }
}
