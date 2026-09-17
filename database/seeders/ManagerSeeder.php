<?php

namespace Database\Seeders;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use Illuminate\Database\Seeder;

final class ManagerSeeder extends Seeder
{
    public function run(
        RegisterActorWithIdentityHandler $register,
        IdentityRepository $identities,
    ): void {
        $email = SeedAccounts::managerEmail();
        $password = SeedAccounts::managerPassword();

        if ($identities->findByEmail($email) !== null) {
            $this->command?->info("Manager already exists: {$email}");

            return;
        }

        $register->handle(
            'managers',
            $email,
            $password,
            issueToken: false,
            name: 'Демо Менеджер',
            phone: '+79001110001',
        );

        $this->command?->info("Manager created: {$email} / {$password}");
    }
}
