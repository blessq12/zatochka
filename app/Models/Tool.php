<?php

namespace App\Models;

use App\Dictionaries\ToolTypeDictionary;
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

    protected $appends = ['tool_type_label'];

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
     * Человекочитаемое название типа инструмента
     */
    public function getToolTypeLabelAttribute(): string
    {
        return ToolTypeDictionary::getLabel($this->tool_type);
    }

    /**
     * Получить полное описание инструмента
     */
    public function getFullDescriptionAttribute(): string
    {
        $typeLabel = $this->tool_type_label ?: $this->tool_type;
        $parts = array_filter([
            $typeLabel,
            $this->quantity > 1 ? "({$this->quantity} шт.)" : null,
        ]);

        return implode(' ', $parts) ?: 'Инструмент';
    }
}
