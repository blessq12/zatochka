<?php

namespace App\Infrastructure\CRM\Model;

use Illuminate\Database\Eloquent\Model;

final class ClientHistoryModel extends Model
{
    protected $table = 'client_history';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'client_id',
        'order_id',
        'note',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }
}
