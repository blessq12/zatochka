<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

final class DemoUsersSeeder extends Seeder
{
    /**
     * Demo credentials for local / staging smoke:
     * - manager@demo.local / password → /manager
     * - master@demo.local / password → /master
     */
    public function run(): void
    {
        foreach ($this->accounts() as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => $account['password'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    /**
     * @return list<array{name: string, email: string, role: UserRole, password: string}>
     */
    private function accounts(): array
    {
        return [
            [
                'name' => 'Demo Manager',
                'email' => 'manager@demo.local',
                'role' => UserRole::Manager,
                'password' => 'password',
            ],
            [
                'name' => 'Demo Master',
                'email' => 'master@demo.local',
                'role' => UserRole::Master,
                'password' => 'password',
            ],
        ];
    }
}
