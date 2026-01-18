<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель для инструментов, принесенных на заточку
 * Не выводится в Filament - используется только для связи с заказами
 */
class Tool extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sharpening_tools';

    protected $fillable = [
        'order_id',
        'tool_type',
        'quantity',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Связи
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope для активных инструментов
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Получить полное описание инструмента
     */
    public function getFullDescriptionAttribute(): string
    {
        $parts = array_filter([
            $this->tool_type,
            $this->quantity > 1 ? "({$this->quantity} шт.)" : null,
        ]);

        return implode(' ', $parts) ?: 'Инструмент';
    }
}
