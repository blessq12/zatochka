<?php

namespace Database\Seeders;

use App\Application\Crm\Command\CreateEquipmentHandler;
use App\Application\Crm\Command\CreateWalkInClientHandler;
use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Infrastructure\Crm\Eloquent\ProfileAdditionalModel;
use Illuminate\Database\Seeder;

/**
 * CRM + Identity: master, registered client, walk-in client.
 */
final class ActorsSeeder extends Seeder
{
    public function run(
        RegisterActorWithIdentityHandler $register,
        CreateWalkInClientHandler $createWalkIn,
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
    ): void {
        $masterId = $this->ensureActor(
            $register,
            $identities,
            $links,
            'masters',
            SeedAccounts::masterEmail(),
            SeedAccounts::masterPassword(),
            'Демо Мастер',
            '+79001110002',
        );

        $clientId = $this->ensureActor(
            $register,
            $identities,
            $links,
            'clients',
            SeedAccounts::clientEmail(),
            SeedAccounts::clientPassword(),
            'Демо Клиент',
            '+79001110003',
        );

        $walkInPhone = '+79001110999';
        if (ProfileAdditionalModel::query()->where('phone', $walkInPhone)->exists()) {
            $this->command?->info('Walk-in client already exists.');
        } else {
            $walkIn = $createWalkIn->handle('Гость Walk-in', $walkInPhone);
            $this->command?->info("Walk-in client id={$walkIn->id}");
        }

        $this->command?->info("Master id={$masterId}: ".SeedAccounts::masterEmail());
        $this->command?->info("Client id={$clientId}: ".SeedAccounts::clientEmail());
    }

    private function ensureActor(
        RegisterActorWithIdentityHandler $register,
        IdentityRepository $identities,
        IdentityActorLinkRepository $links,
        string $type,
        string $email,
        string $password,
        string $name,
        string $phone,
    ): int {
        $existing = $identities->findByEmail($email);
        if ($existing !== null) {
            $link = $links->findByIdentityId((int) $existing->id());
            if ($link === null) {
                throw new \RuntimeException("Identity {$email} has no actor link.");
            }

            $this->command?->info("Actor already exists: {$email}");

            return $link->actorId;
        }

        $result = $register->handle(
            $type,
            $email,
            $password,
            issueToken: false,
            name: $name,
            phone: $phone,
        );

        return $result->actor->id;
    }
}
