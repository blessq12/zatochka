<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Repair extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'problem_description',
        'price',
        'status',
        'comments',
        'completed_works',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'completed_works' => 'array',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
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
        if (! $this->started_at || ! $this->completed_at) {
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'order_id',
                'problem_description',
                'price',
                'status',
                'comments',
                'completed_works',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Ремонт создан',
                'updated' => 'Ремонт обновлен',
                'deleted' => 'Ремонт удален',
                default => "Ремонт {$eventName}",
            });
    }
}
