<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Repair extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'number',
        'order_id',
        'master_id',
        'status',
        'description',
        'diagnosis',
        'work_performed',
        'notes',
        'started_at',
        'completed_at',
        'estimated_completion',
        'parts_used',
        'additional_data',
        'work_time_minutes',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'parts_used' => 'array',
        'additional_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_completion' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouse()
    {
        return $this->hasOneThrough(Warehouse::class, Order::class, 'id', 'branch_id', 'order_id', 'branch_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['diagnosis', 'in_progress', 'waiting_parts', 'testing']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('estimated_completion', '<', now())
            ->where('status', '!=', 'completed');
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Ожидает',
            'diagnosis' => 'Диагностика',
            'in_progress' => 'В работе',
            'waiting_parts' => 'Ожидание запчастей',
            'testing' => 'Тестирование',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'diagnosis' => 'blue',
            'in_progress' => 'yellow',
            'waiting_parts' => 'orange',
            'testing' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->estimated_completion &&
            $this->estimated_completion < now() &&
            $this->status !== 'completed';
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInMinutes($this->started_at);
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('before_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile(false);

        $this->addMediaCollection('after_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile(false);
    }
}
