<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'working_hours',
        'latitude',
        'longitude',
        'description',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }


    // Scope для активных филиалов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
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
        return $this->is_active && ! $this->is_deleted;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }
}
