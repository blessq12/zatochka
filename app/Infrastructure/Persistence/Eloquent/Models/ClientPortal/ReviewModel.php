<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\ClientPortal;

use App\Domain\ClientPortal\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;

class ReviewModel extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'order_id',
        'client_id',
        'rating',
        'comment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
        ];
    }
}
