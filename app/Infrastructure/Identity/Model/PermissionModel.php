<?php

namespace App\Infrastructure\Identity\Model;

use Illuminate\Database\Eloquent\Model;

final class PermissionModel extends Model
{
    protected $table = 'permissions';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'code', 'description'];
}
