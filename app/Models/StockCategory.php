<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'name',
        'description',
        'color',
        'sort_order',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class, 'category_id');
    }

    // Scope для активных категорий
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    // Scope для сортировки по порядку
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Методы
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function markDeleted(): void
    {
        $this->update(['is_deleted' => true]);
    }

    public function updateSortOrder(int $newOrder): void
    {
        $this->update(['sort_order' => $newOrder]);
    }

    // Получить количество товаров в категории
    public function getItemsCount(): int
    {
        return $this->stockItems()
            ->where('is_active', true)
            ->count();
    }

    // Получить общую стоимость товаров в категории
    public function getTotalValue(): float
    {
        return $this->stockItems()
            ->where('is_active', true)
            ->get()
            ->sum(function ($item) {
                return $item->quantity * ($item->purchase_price ?? 0);
            });
    }

    // Получить товары с низким запасом в категории
    public function getLowStockItems()
    {
        return $this->stockItems()
            ->where('is_active', true)
            ->whereRaw('quantity <= min_stock')
            ->get();
    }

    // Проверить, есть ли товары в категории
    public function hasItems(): bool
    {
        return $this->stockItems()->where('is_active', true)->exists();
    }

    // Получить цвет для UI (с fallback)
    public function getDisplayColor(): string
    {
        return $this->color ?? '#6B7280'; // Серый цвет по умолчанию
    }
}
