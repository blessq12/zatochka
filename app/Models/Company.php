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
        'description',
        'website',
        'phone',
        'email',
        'bank_name',
        'bank_bik',
        'bank_account',
        'bank_cor_account',
        'logo_path',
        'additional_data',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch()
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }

    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    // Scope для активных компаний
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    // Методы для работы с банковскими данными
    public function getBankInnAttribute()
    {
        return $this->additional_data['bank_inn'] ?? null;
    }

    public function getBankKppAttribute()
    {
        return $this->additional_data['bank_kpp'] ?? null;
    }

    public function getShortLegalNameAttribute()
    {
        return $this->additional_data['short_legal_name'] ?? null;
    }

    public function getAccountOpenDateAttribute()
    {
        return $this->additional_data['account_open_date'] ?? null;
    }

    // Методы для работы со статусом
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function markDeleted()
    {
        $this->update(['is_deleted' => true, 'is_active' => false]);
    }

    // Проверки статуса
    public function isActive(): bool
    {
        return $this->is_active && !$this->is_deleted;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    public function hasMainBranch(): bool
    {
        return $this->branches()->where('is_main', true)->exists();
    }

    public function getMainBranch()
    {
        return $this->branches()->where('is_main', true)->first();
    }
}
