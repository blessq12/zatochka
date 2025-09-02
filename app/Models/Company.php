<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'inn',
        'kpp',
        'ogrn',
        'legal_address',
        'website',
        'bank_name',
        'bank_bik',
        'bank_account',
        'bank_cor_account',
        'logo_path',
        'additional_data',
        'is_deleted',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    // Scope для активных компаний
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
