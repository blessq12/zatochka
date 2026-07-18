<?php

namespace App\Infrastructure\CRM\Model;

use Illuminate\Database\Eloquent\Model;

final class ClientLeadModel extends Model
{
    protected $table = 'client_leads';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'client_id',
        'service_types',
        'comment',
        'intake_data',
        'needs_delivery',
        'delivery_address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'intake_data' => 'array',
            'needs_delivery' => 'boolean',
        ];
    }
}
