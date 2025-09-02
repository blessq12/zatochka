<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class BaseUserAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $masterRole  = Role::firstOrCreate(['name' => 'master']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.test'],
            ['name' => 'Manager', 'password' => bcrypt('password')]
        );
        $manager->assignRole($managerRole);

        $master = User::firstOrCreate(
            ['email' => 'master@crm.test'],
            ['name' => 'Master', 'password' => bcrypt('password')]
        );
        $master->assignRole($masterRole);
    }
}
