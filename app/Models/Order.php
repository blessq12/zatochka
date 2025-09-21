<?php

namespace App\Models;

// Удалены Enum'ы - теперь используются строки в БД
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    // Константы статусов заказов
    public const STATUS_NEW = 'new';

    public const STATUS_CONSULTATION = 'consultation';

    public const STATUS_DIAGNOSTIC = 'diagnostic';

    public const STATUS_IN_WORK = 'in_work';

    public const STATUS_WAITING_PARTS = 'waiting_parts';

    public const STATUS_READY = 'ready';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_CANCELLED = 'cancelled';

    // Константы типов заказов
    public const TYPE_REPAIR = 'repair';

    public const TYPE_SHARPENING = 'sharpening';

    public const TYPE_DIAGNOSTIC = 'diagnostic';

    public const TYPE_REPLACEMENT = 'replacement';

    public const TYPE_MAINTENANCE = 'maintenance';

    public const TYPE_CONSULTATION = 'consultation';

    public const TYPE_WARRANTY = 'warranty';

    // Константы срочности
    public const URGENCY_NORMAL = 'normal';

    public const URGENCY_URGENT = 'urgent';

    protected $fillable = [
        'client_id',
        'branch_id',
        'manager_id',
        'order_number',
        'type',
        'status',
        'urgency',
        'discount_id',
        'estimated_price',
        'actual_price',
        'internal_notes',
        'problem_description',
        'is_deleted',
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'actual_price' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse()
    {
        return $this->hasOneThrough(Warehouse::class, Branch::class, 'id', 'branch_id', 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function discount()
    {
        return $this->belongsTo(DiscountRule::class, 'discount_id');
    }

    public function repair()
    {
        return $this->hasOne(Repair::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function bonusTransactions()
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    // public function inventoryTransactions()
    // {
    //     return $this->hasMany(InventoryTransaction::class);
    // }

    // Scope для активных заказов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для заказов по статусу
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope для срочных заказов
    public function scopeUrgent($query)
    {
        return $query->where('urgency', 'urgent');
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

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    // Методы для работы со статусами
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isInWork(): bool
    {
        return $this->status === self::STATUS_IN_WORK;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_CANCELLED]);
    }

    public function isManagerStatus(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CONSULTATION, self::STATUS_DIAGNOSTIC]);
    }

    public function isWorkshopStatus(): bool
    {
        return in_array($this->status, [self::STATUS_IN_WORK, self::STATUS_WAITING_PARTS, self::STATUS_READY]);
    }

    public function canTransferToWorkshop(): bool
    {
        // TODO: Implement workshop transfer logic
        return false;
    }

    public function canTransferToManager(): bool
    {
        // TODO: Implement manager transfer logic
        return false;
    }

    public function getAvailableTransitions(): array
    {
        // TODO: Implement status transitions logic
        return [];
    }

    // Статические методы для получения доступных значений
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_CONSULTATION => 'Консультация',
            self::STATUS_DIAGNOSTIC => 'Диагностика',
            self::STATUS_IN_WORK => 'В работе',
            self::STATUS_WAITING_PARTS => 'Ожидание запчастей',
            self::STATUS_READY => 'Готов',
            self::STATUS_ISSUED => 'Выдан',
            self::STATUS_CANCELLED => 'Отменен',
        ];
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_REPAIR => 'Ремонт',
            self::TYPE_SHARPENING => 'Заточка',
            self::TYPE_DIAGNOSTIC => 'Диагностика',
            self::TYPE_REPLACEMENT => 'Замена',
            self::TYPE_MAINTENANCE => 'Обслуживание',
            self::TYPE_CONSULTATION => 'Консультация',
            self::TYPE_WARRANTY => 'Гарантийный',
        ];
    }

    public static function getAvailableUrgencies(): array
    {
        return [
            self::URGENCY_NORMAL => 'Обычный',
            self::URGENCY_URGENT => 'Срочный',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id',
                'branch_id',
                'manager_id',
                'type',
                'status',
                'urgency',
                'estimated_price',
                'actual_price',
                'internal_notes',
                'problem_description',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Заказ создан',
                'updated' => 'Заказ обновлен',
                'deleted' => 'Заказ удален',
                default => "Заказ {$eventName}",
            });
    }

    /**
     * Генерирует уникальный номер заказа
     */
    public static function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return 'ORD-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot метод для автоматической генерации номера заказа
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }
}
