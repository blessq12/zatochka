<?php

namespace Database\Seeders;

use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Domain\Identity\Repository\IdentityRepository;
use Illuminate\Database\Seeder;

final class ManagerSeeder extends Seeder
{
    public function run(RegisterActorWithIdentityHandler $register, IdentityRepository $identities): void
    {
        $email = env('SEED_MANAGER_EMAIL', 'manager@zatochka.local');
        $password = env('SEED_MANAGER_PASSWORD', 'password123');

        if ($identities->findByEmail($email) !== null) {
            $this->command?->info("Manager already exists: {$email}");

            return;
        }

        $register->handle('managers', $email, $password, issueToken: false);

        $this->command?->info("Manager created: {$email}");
    }
}
