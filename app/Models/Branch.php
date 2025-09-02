<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'working_hours',
        'latitude',
        'longitude',
        'description',
        'additional_data',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'additional_data' => 'array',
    ];

    // Связи
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    // Scope для активных филиалов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }
}
