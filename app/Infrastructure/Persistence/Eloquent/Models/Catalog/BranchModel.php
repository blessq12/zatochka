<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\Catalog;

use App\Domain\Catalog\Enums\PriceType;
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
