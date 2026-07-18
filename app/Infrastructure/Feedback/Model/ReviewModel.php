<?php

namespace App\Infrastructure\Feedback\Model;

use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Order\Model\OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReviewModel extends Model
{
    protected $table = 'reviews';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'order_id',
        'client_id',
        'rating',
        'comment',
        'manager_reply',
        'status',
        'moderated_by',
        'submitted_at',
        'moderated_at',
        'hidden_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'submitted_at' => 'datetime',
            'moderated_at' => 'datetime',
            'hidden_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }
}
