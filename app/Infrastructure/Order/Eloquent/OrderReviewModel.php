<?php

namespace App\Infrastructure\Order\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderReviewModel extends Model
{
    protected $table = 'order_reviews';

    protected $fillable = [
        'order_id',
        'client_id',
        'rating',
        'text',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
