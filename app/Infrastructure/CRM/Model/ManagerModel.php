<?php

namespace App\Infrastructure\CRM\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 */
final class ManagerModel extends Model
{
    protected $table = 'managers';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'email',
    ];
}
