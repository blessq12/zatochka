<?php

namespace App\Infrastructure\Company\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BranchModel extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
