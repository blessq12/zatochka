<?php

namespace App\Infrastructure\Feedback\Model;

use Illuminate\Database\Eloquent\Model;

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
}
