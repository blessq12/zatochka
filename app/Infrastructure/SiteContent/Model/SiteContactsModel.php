<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class SiteContactsModel extends Model
{
    protected $table = 'site_contacts';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'contact_person',
        'phone',
        'email',
        'address_main',
        'entrance_directions',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }
}
