<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    // Scope для активных типов инструментов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
