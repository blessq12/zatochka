<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class FillUserUuids extends Command
{
    protected $signature = 'users:fill-uuids';

    protected $description = 'Fill UUID field for existing users';

    public function handle()
    {
        $users = User::whereNull('uuid')->get();

        if ($users->isEmpty()) {
            $this->info('All users already have UUIDs.');

            return 0;
        }

        $this->info("Found {$users->count()} users without UUIDs. Filling...");

        foreach ($users as $user) {
            $user->uuid = Uuid::uuid4()->toString();
            $user->save();
            $this->line("Filled UUID for user: {$user->email}");
        }

        $this->info('Successfully filled UUIDs for all users.');

        return 0;
    }
}
