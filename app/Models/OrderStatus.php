<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'is_final',
        'sort_order',
        'is_deleted',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id');
    }

    public function fromTransitions()
    {
        return $this->hasMany(OrderStatusTransition::class, 'from_status_id');
    }

    public function toTransitions()
    {
        return $this->hasMany(OrderStatusTransition::class, 'to_status_id');
    }

    // Scope для активных статусов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для статусов по типу
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
