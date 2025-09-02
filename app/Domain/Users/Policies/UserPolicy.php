<?php

namespace App\Domain\Users\Policies;

use App\Models\User as EloquentUser;

class UserPolicy
{
    public function viewAny(EloquentUser $actor): bool
    {
        return $actor->can('users.view');
    }

    public function view(EloquentUser $actor, EloquentUser $target): bool
    {
        return $actor->id === $target->id || $actor->can('users.view');
    }

    public function update(EloquentUser $actor, EloquentUser $target): bool
    {
        if ($actor->id === $target->id) {
            return true; // allow self-update (profile)
        }
        return $actor->can('users.update');
    }

    public function assignRoles(EloquentUser $actor, EloquentUser $target): bool
    {
        // Disallow self role changes to avoid privilege escalation
        if ($actor->id === $target->id) {
            return false;
        }
        return $actor->can('users.assign-roles');
    }

    public function delete(EloquentUser $actor, EloquentUser $target): bool
    {
        if ($actor->id === $target->id) {
            return false; // cannot delete yourself
        }
        return $actor->can('users.delete');
    }
}
