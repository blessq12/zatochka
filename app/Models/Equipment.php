<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'serial_number',
        'manufacturer',
        'brand', // Существующее поле в БД
        'model',
        'client_id',
        'equipment_type_id', // Существующее поле в БД
        'description',
        'purchase_date',
        'warranty_expiry',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'is_active' => 'boolean',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scope для активного оборудования
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для поиска по серийному номеру
    public function scopeBySerialNumber($query, string $serialNumber)
    {
        return $query->where('serial_number', $serialNumber);
    }

    // Accessor для полного названия
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->manufacturer ?? $this->brand, // Используем manufacturer или brand
            $this->model,
            $this->name,
        ]);

        return implode(' ', $parts) ?: $this->name;
    }

    // Accessor для manufacturer (используем brand если manufacturer нет)
    public function getManufacturerAttribute($value)
    {
        return $value ?? $this->attributes['brand'] ?? null;
    }

    // Методы для работы со статусом
    public function markDeleted()
    {
        $this->update(['is_deleted' => true]);
    }

    public function isActive(): bool
    {
        return !$this->is_deleted;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    /**
     * Получить количество обращений по этому оборудованию
     */
    public function getOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Получить последний заказ по этому оборудованию
     */
    public function getLastOrderAttribute(): ?Order
    {
        return $this->orders()->latest()->first();
    }
}
