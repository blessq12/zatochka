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
        'phone_tel',
        'email',
        'address_main',
        'address_details',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'address_details' => 'array',
            'social_links' => 'array',
        ];
    }
}
