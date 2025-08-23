<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\HasReviews;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasReviews;

    protected $fillable = [
        'name',
        'legal_name',
        'inn',
        'kpp',
        'ogrn',
        'address',
        'legal_address',
        'phone',
        'email',
        'website',
        'bank_name',
        'bank_bik',
        'bank_account',
        'bank_cor_account',
        'description',
        'logo_path',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
