<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'category_id',
        'name',
        'sku',
        'description',
        'purchase_price',
        'retail_price',
        'quantity',
        'min_stock',
        'unit',
        'supplier',
        'manufacturer',
        'model',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category()
    {
        return $this->belongsTo(StockCategory::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'stock_item_id');
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Warehouse::class, 'id', 'id', 'warehouse_id', 'branch_id');
    }

    // Scope для активных товаров
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    // Scope для товаров с низким запасом
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_stock');
    }

    // Scope для товаров по складу
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // Scope для товаров по категории
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Scope для поиска по SKU или названию
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
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

    // Операции с остатками
    public function addStock(int $amount): void
    {
        $this->increment('quantity', $amount);
    }

    public function deductStock(int $amount): void
    {
        if ($this->quantity >= $amount) {
            $this->decrement('quantity', $amount);
        } else {
            throw new \Exception('Недостаточно товара на складе');
        }
    }

    public function adjustStock(int $newQuantity, string $reason = ''): void
    {
        $oldQuantity = $this->quantity;
        $this->update(['quantity' => $newQuantity]);

        // Здесь можно добавить логирование изменения
    }

    // Проверки
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function hasStock(int $amount): bool
    {
        return $this->quantity >= $amount;
    }

    // Расчёты
    public function getTotalValue(): float
    {
        return $this->quantity * ($this->purchase_price ?? 0);
    }

    public function getRetailValue(): float
    {
        return $this->quantity * ($this->retail_price ?? 0);
    }

    public function getProfitMargin(): float
    {
        if (!$this->purchase_price || !$this->retail_price) {
            return 0;
        }

        return (($this->retail_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    // Получить последние движения
    public function getRecentMovements(int $limit = 10)
    {
        return $this->stockMovements()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // Получить историю движений по типу
    public function getMovementsByType(string $type)
    {
        return $this->stockMovements()
            ->where('movement_type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
