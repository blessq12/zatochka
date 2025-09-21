<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_id',
        'rating',
        'comment',
        'is_approved',
        'is_visible',
        'reply',
        'metadata',
        'is_deleted',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_visible' => 'boolean',
        'metadata' => 'array',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope для активных отзывов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для одобренных отзывов
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
