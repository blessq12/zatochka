<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class CompanyProfileModel extends Model
{
    protected $table = 'site_company_profiles';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'owner_name',
        'inn',
        'ogrn',
        'legal_address',
        'actual_address',
    ];
}
