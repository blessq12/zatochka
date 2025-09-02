<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'conditions',
        'active_from',
        'active_to',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'conditions' => 'array',
        'active_from' => 'datetime',
        'active_to' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function orders()
    {
        return $this->hasMany(Order::class, 'discount_id');
    }

    // Scope для активных правил скидок
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('active_from')
                    ->orWhere('active_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('active_to')
                    ->orWhere('active_to', '>=', now());
            });
    }
}
