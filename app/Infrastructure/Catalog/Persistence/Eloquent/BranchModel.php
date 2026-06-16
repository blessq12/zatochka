<?php

namespace App\Infrastructure\Catalog\Persistence\Eloquent;

use App\Domain\Catalog\Enum\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
