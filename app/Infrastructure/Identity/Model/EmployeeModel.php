<?php

namespace App\Infrastructure\Identity\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class EmployeeModel extends Model
{
    protected $table = 'employees';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'email', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'employee_role', 'employee_id', 'role_id');
    }
}
