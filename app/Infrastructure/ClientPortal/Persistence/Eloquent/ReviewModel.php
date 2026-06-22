<?php

namespace App\Infrastructure\ClientPortal\Persistence\Eloquent;

use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class);
    }
}
