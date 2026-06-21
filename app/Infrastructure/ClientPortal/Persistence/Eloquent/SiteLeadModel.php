<?php

namespace App\Infrastructure\ClientPortal\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class SiteLeadModel extends Model
{
    protected $table = 'site_leads';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'service_types',
        'comment',
        'intake_data',
        'needs_delivery',
        'delivery_address',
        'converted',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'intake_data' => 'array',
            'needs_delivery' => 'boolean',
            'converted' => 'boolean',
        ];
    }
}
