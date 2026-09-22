<?php

namespace App\Infrastructure\Order\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderCommentModel extends Model
{
    protected $table = 'order_comments';

    protected $fillable = [
        'order_id',
        'author_type',
        'author_id',
        'body',
        'kind',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
