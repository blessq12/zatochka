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
        'bank_name',
        'bank_bik',
        'bank_account',
        'bank_cor_account',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
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
        return $this->branches()->first();
    }

    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    // Scope для активных компаний
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Методы для работы со статусом
    public function markDeleted()
    {
        $this->update(['is_deleted' => true]);
    }

    // Проверки статуса
    public function isActive(): bool
    {
        return ! $this->is_deleted;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    public function hasMainBranch(): bool
    {
        return $this->branches()->exists();
    }

    public function getMainBranch()
    {
        return $this->branches()->first();
    }
}
