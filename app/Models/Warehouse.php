<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scope для активных складов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    // Scope для складов по филиалу
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
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

    // Получить общее количество товаров на складе
    public function getTotalItemsCount(): int
    {
        return $this->stockItems()->sum('quantity');
    }

    // Получить общую стоимость товаров на складе
    public function getTotalStockValue(): float
    {
        return $this->stockItems()
            ->where('is_active', true)
            ->get()
            ->sum(function ($item) {
                return $item->quantity * ($item->purchase_price ?? 0);
            });
    }

    // Получить товары с низким запасом
    public function getLowStockItems()
    {
        return $this->stockItems()
            ->where('is_active', true)
            ->whereRaw('quantity <= min_stock')
            ->get();
    }

    // Получить товары по категории
    public function getItemsByCategory($categoryId)
    {
        return $this->stockItems()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();
    }
}
