<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Repair extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'order_id',
        'description',
        'used_materials',
        'work_time_minutes',
        'price',
        'is_deleted',
    ];

    protected $casts = [
        'used_materials' => 'array',
        'work_time_minutes' => 'integer',
        'price' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope для активных работ
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // MediaLibrary конфигурация
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('before_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        $this->addMediaCollection('after_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        $this->addMediaCollection('work_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
