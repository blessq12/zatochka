<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FillRoleGuards extends Command
{
    protected $signature = 'roles:fill-guards';

    protected $description = 'Fill guard_name for existing roles';

    public function handle()
    {
        $roles = Role::whereNull('guard_name')->orWhere('guard_name', '')->get();

        if ($roles->isEmpty()) {
            $this->info('All roles already have guard_name set.');
            return 0;
        }

        $this->info("Found {$roles->count()} roles without guard_name. Setting to 'manager'...");

        foreach ($roles as $role) {
            $role->guard_name = 'manager';
            $role->save();
            $this->line("Set guard_name for role: {$role->name}");
        }

        $this->info('Successfully set guard_name for all roles.');
        return 0;
    }
}
