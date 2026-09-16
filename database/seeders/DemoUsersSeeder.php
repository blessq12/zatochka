<?php

namespace Database\Seeders;

use App\Infrastructure\Identity\Model\ManagerModel;
use App\Infrastructure\Identity\Model\MasterModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoUsersSeeder extends Seeder
{
    /**
     * Demo credentials for local / staging smoke:
     * - manager@demo.local / password → /manager
     * - master@demo.local / password → /master
     */
    public function run(): void
    {
        ManagerModel::query()->updateOrCreate(
            ['email' => 'manager@demo.local'],
            [
                'id' => 1,
                'name' => 'Demo Manager',
                'password' => Hash::make('password'),
            ],
        );

        MasterModel::query()->updateOrCreate(
            ['email' => 'master@demo.local'],
            [
                'id' => 2,
                'name' => 'Demo Master',
                'password' => Hash::make('password'),
            ],
        );
    }
}
