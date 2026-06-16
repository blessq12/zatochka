<?php

namespace App\Domain\ClientPortal\Models;

use App\Domain\ClientPortal\Enums\ReviewStatus;
use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
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
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
