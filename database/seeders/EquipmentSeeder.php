<?php

namespace Database\Seeders;

use App\Application\Crm\Command\CreateEquipmentHandler;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Infrastructure\Crm\Eloquent\EquipmentModel;
use Illuminate\Database\Seeder;

final class EquipmentSeeder extends Seeder
{
    public function run(
        CreateEquipmentHandler $createEquipment,
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
    ): void {
        if (EquipmentModel::query()->exists()) {
            $this->command?->info('Equipment already seeded.');

            return;
        }

        $clientId = $this->clientId($identities, $links);

        $eq1 = $createEquipment->handle(
            $clientId,
            'Фрезерный станок',
            'Haas',
            'cnc',
            [
                ['name' => 'Шпиндель', 'serial_number' => 'SP-1001'],
                ['name' => 'ЧПУ блок', 'serial_number' => 'CNC-2002'],
            ],
        );

        $eq2 = $createEquipment->handle(
            $clientId,
            'Токарный станок',
            'DMG',
            'lathe',
            [
                ['name' => 'Патрон', 'serial_number' => 'CH-3003'],
            ],
        );

        $this->command?->info("Equipment seeded: #{$eq1->id}, #{$eq2->id}");
    }

    private function clientId(
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
    ): int {
        $identity = $identities->findByEmail(SeedAccounts::clientEmail());
        if ($identity === null) {
            throw new \RuntimeException('Seed client not found. Run ActorsSeeder first.');
        }

        $link = $links->findByIdentityId((int) $identity->id());
        if ($link === null) {
            throw new \RuntimeException('Seed client has no actor link.');
        }

        return $link->actorId;
    }
}
