<?php

namespace Database\Seeders;

use App\Infrastructure\CRM\Model\ManagerModel;
use App\Infrastructure\CRM\Model\MasterModel;
use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Infrastructure\Identity\Model\MasterAccountModel;
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
            ['id' => 1],
            [
                'name' => 'Demo Manager',
                'email' => 'manager@demo.local',
            ],
        );

        ManagerAccountModel::query()->updateOrCreate(
            ['id' => 1],
            [
                'email' => 'manager@demo.local',
                'password' => Hash::make('password'),
            ],
        );

        MasterModel::query()->updateOrCreate(
            ['id' => 2],
            [
                'name' => 'Demo Master',
                'email' => 'master@demo.local',
            ],
        );

        MasterAccountModel::query()->updateOrCreate(
            ['id' => 2],
            [
                'email' => 'master@demo.local',
                'password' => Hash::make('password'),
            ],
        );

        $maxStaffId = max(
            (int) ManagerModel::query()->max('id'),
            (int) MasterModel::query()->max('id'),
            0,
        );

        \Illuminate\Support\Facades\DB::table('entity_id_sequences')->updateOrInsert(
            ['name' => 'staff'],
            ['next_value' => $maxStaffId + 1],
        );
    }
}
