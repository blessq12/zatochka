<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class ReceptionDataModel extends Model
{
    protected $table = 'reception_data';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'order_item_id',
        'condition_description',
        'visual_notes',
        'attachment_refs',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'attachment_refs' => 'array',
            'received_at' => 'datetime',
        ];
    }
}
