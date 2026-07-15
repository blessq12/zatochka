<?php

namespace App\Infrastructure\Identity\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class RoleModel extends Model
{
    protected $table = 'roles';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionModel::class, 'role_permission', 'role_id', 'permission_id');
    }
}
