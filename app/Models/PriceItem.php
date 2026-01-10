<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type',
        'category_title',
        'name',
        'description',
        'price',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Константы типов услуг
    public const TYPE_SHARPENING = 'sharpening';
    public const TYPE_REPAIR = 'repair';

    /**
     * Скоуп для активных услуг
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Скоуп для фильтрации по типу услуги
     */
    public function scopeByServiceType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    /**
     * Скоуп для сортировки
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('category_title')->orderBy('name');
    }
}
