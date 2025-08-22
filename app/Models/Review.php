<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'order_id',
        'entity_id',
        'entity_type',
        'rating',
        'comment',
        'source',
        'status',
        'reply',
        'metadata',
        'target_model',
        'target_record'
    ];

    protected $casts = [
        'rating' => 'integer',
        'metadata' => 'array',
    ];

    // Связи
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    // Скоупы для фильтрации
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeOfSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeWithRating($query)
    {
        return $query->whereNotNull('rating');
    }

    // Методы для работы с рейтингом
    public function getStarsAttribute(): string
    {
        if (!$this->rating) {
            return '';
        }
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->rating ?? 0;
    }

    // Методы для работы со статусом
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }

    // Методы для работы с метаданными
    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    // Статические методы для получения статистики
    public static function getAverageRatingByType(string $type): float
    {
        return static::ofType($type)
            ->approved()
            ->withRating()
            ->avg('rating') ?? 0;
    }

    public static function getCountByStatus(string $status): int
    {
        return static::ofStatus($status)->count();
    }

    public static function getCountByType(string $type): int
    {
        return static::ofType($type)->count();
    }
}
