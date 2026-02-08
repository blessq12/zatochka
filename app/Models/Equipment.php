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
        'serial_number' => 'array', // [{ name: string, serial_number: string }, ...]
        'is_deleted' => 'boolean',
        'is_active' => 'boolean',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    protected $attributes = [
        'serial_number' => '[]',
    ];

    protected $appends = [
        'serial_numbers_display',
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

    /** Поиск по серийному номеру (в любой части оборудования) */
    public function scopeBySerialNumber($query, string $serialNumber)
    {
        return $query->whereRaw('serial_number LIKE ?', ['%' . addslashes($serialNumber) . '%']);
    }

    /** Строка для отображения серийников: "Часть1: SN1; Часть2: SN2" или "SN1, SN2" */
    public function getSerialNumbersDisplayAttribute(): string
    {
        $items = $this->serial_number ?? [];
        if (!is_array($items) || count($items) === 0) {
            return '';
        }
        $parts = [];
        foreach ($items as $item) {
            $name = trim($item['name'] ?? '');
            $sn = trim($item['serial_number'] ?? '');
            $parts[] = $name !== '' ? "{$name}: {$sn}" : $sn;
        }
        return implode('; ', array_filter($parts));
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
