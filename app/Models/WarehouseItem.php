<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_category_id',
        'name',
        'article',
        'description',
        'unit',
        'quantity',
        'reserved_quantity',
        'min_quantity',
        'price',
        'location',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'min_quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Генерирует уникальный артикул товара
     */
    public static function generateArticle(): string
    {
        $prefix = 'ART';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            // Генерируем артикул: ART-YYYYMMDD-NNNN
            $date = date('Ymd');
            $count = static::whereDate('created_at', today())->count() + 1;
            $article = $prefix . '-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Проверяем уникальность
            $exists = static::where('article', $article)->exists();
            $attempt++;

            if (!$exists) {
                return $article;
            }

            // Если артикул существует, увеличиваем счетчик
            $count++;
        } while ($attempt < $maxAttempts);

        // Если не удалось за 100 попыток, добавляем случайное число
        return $prefix . '-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
    }

    /**
     * Boot метод для автоматической генерации артикула и форматирования названия
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->article)) {
                $item->article = static::generateArticle();
            }
            // Делаем первую букву названия заглавной
            if (!empty($item->name)) {
                $item->name = static::capitalizeFirstLetter($item->name);
            }
        });

        static::updating(function ($item) {
            // Делаем первую букву названия заглавной при обновлении
            if ($item->isDirty('name') && !empty($item->name)) {
                $item->name = static::capitalizeFirstLetter($item->name);
            }
        });
    }

    /**
     * Делает первую букву строки заглавной (поддержка UTF-8)
     */
    protected static function capitalizeFirstLetter(string $string): string
    {
        if (empty($string)) {
            return $string;
        }

        $firstChar = mb_substr($string, 0, 1, 'UTF-8');
        $rest = mb_substr($string, 1, null, 'UTF-8');

        return mb_strtoupper($firstChar, 'UTF-8') . mb_strtolower($rest, 'UTF-8');
    }

    /**
     * Связь с категорией
     */
    public function category()
    {
        return $this->belongsTo(WarehouseCategory::class, 'warehouse_category_id');
    }

    /**
     * Связь с работами (списанные товары)
     */
    public function orderWorks()
    {
        return $this->belongsToMany(OrderWork::class, 'work_warehouse_items', 'warehouse_item_id', 'work_id')
            ->withPivot('quantity', 'price', 'notes')
            ->withTimestamps();
    }

    // Alias для обратной совместимости
    public function works()
    {
        return $this->orderWorks();
    }

    /**
     * Scope для активных товаров
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для товаров с низким остатком
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    /**
     * Scope для товаров в наличии
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Получить доступное количество (количество минус резерв)
     */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Проверка доступности количества (с учетом резерва)
     */
    public function hasStock($requiredQuantity): bool
    {
        return $this->available_quantity >= $requiredQuantity;
    }

    /**
     * Зарезервировать товар
     */
    public function reserve($quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        $this->reserved_quantity += $quantity;
        return $this->save();
    }

    /**
     * Снять резерв с товара
     */
    public function releaseReserve($quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }

        $this->reserved_quantity -= $quantity;
        return $this->save();
    }

    /**
     * Списать товар со склада (уменьшает quantity и reserved_quantity)
     */
    public function decreaseQuantity($quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        // Сначала уменьшаем резерв, потом основное количество
        $fromReserve = min($quantity, $this->reserved_quantity);
        $fromQuantity = $quantity - $fromReserve;

        $this->reserved_quantity -= $fromReserve;
        $this->quantity -= $fromQuantity;

        return $this->save();
    }

    /**
     * Вернуть товар на склад
     */
    public function increaseQuantity($quantity): bool
    {
        $this->quantity += $quantity;
        return $this->save();
    }

    /**
     * Проверка низкого остатка
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Проверка, есть ли резерв
     */
    public function hasReserve(): bool
    {
        return $this->reserved_quantity > 0;
    }
}
