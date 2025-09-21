<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tool extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'equipment_type_id',
        'serial_number',
        'brand',
        'model',
        'description',
        'purchase_date',
        'warranty_expiry',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    // Scope для активных инструментов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }


    // MediaLibrary конфигурация
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        $this->addMediaCollection('manuals')
            ->acceptsMimeTypes(['application/pdf']);

        $this->addMediaCollection('warranty_documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
