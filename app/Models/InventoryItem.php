<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'quantity',
        'unit',
        'min_stock',
        'is_deleted',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    // Scope для активных товаров
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для товаров с низким запасом
    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<=', 'min_stock');
    }
}
