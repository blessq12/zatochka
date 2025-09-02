<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scope для активных типов услуг
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
