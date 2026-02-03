<?php

namespace App\Models;

// Удалены Enum'ы - теперь используются строки в БД
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public const STATUS_IN_WORK = 'in_work';

    public const STATUS_WAITING_PARTS = 'waiting_parts';

    public const STATUS_READY = 'ready';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_CANCELLED = 'cancelled';

    // Константы типов услуг (service_type)
    public const TYPE_REPAIR = 'repair';

    public const TYPE_SHARPENING = 'sharpening';

    public const TYPE_DIAGNOSTIC = 'diagnostic';

    public const TYPE_REPLACEMENT = 'replacement';

    public const TYPE_MAINTENANCE = 'maintenance';

    public const TYPE_CONSULTATION = 'consultation';

    public const TYPE_WARRANTY = 'warranty';

    // Константы типов оплаты
    public const PAYMENT_TYPE_PAID = 'paid';

    public const PAYMENT_TYPE_WARRANTY = 'warranty';

    // Константы срочности
    public const URGENCY_NORMAL = 'normal';

    public const URGENCY_URGENT = 'urgent';

    // Источник клиента (откуда пришёл)
    public const SOURCE_SOCIAL = 'social';

    public const SOURCE_OUTDOOR = 'outdoor';

    public const SOURCE_RECOMMENDATION = 'recommendation';

    protected $fillable = [
        'client_id',
        'branch_id',
        'equipment_id',
        'manager_id',
        'master_id',
        'order_number',
        'service_type',
        'status',
        'urgency',
        'client_source',
        'discount_id',
        'price',
        'internal_notes',
        'problem_description',
        'delivery_address',
        'needs_delivery',
        'order_payment_type',
        'is_deleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    protected $appends = ['calculated_price'];

    /**
     * Стоимость заказа = сумма работ + сумма материалов (вычисляемое свойство)
     */
    public function getCalculatedPriceAttribute(): float
    {
        $this->loadMissing(['orderWorks', 'orderMaterials']);
        $worksTotal = $this->orderWorks->sum('work_price');
        $materialsTotal = $this->orderMaterials->sum(fn($m) => $m->quantity * (float)($m->price ?? 0));
        return round($worksTotal + $materialsTotal, 2);
    }

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function master()
    {
        return $this->belongsTo(\App\Models\Master::class, 'master_id');
    }

    public function orderWorks()
    {
        return $this->hasMany(OrderWork::class, 'order_id')->where('is_deleted', false);
    }

    // Alias для обратной совместимости
    public function works()
    {
        return $this->orderWorks();
    }

    /**
     * Получить все материалы всех работ заказа
     */
    public function orderMaterials()
    {
        return $this->hasMany(OrderWorkMaterial::class, 'order_id');
    }

    /**
     * Инструменты для заточки (только для заказов типа sharpening)
     */
    public function tools()
    {
        return $this->hasMany(Tool::class, 'order_id');
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
        return $this->status === self::STATUS_NEW;
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

    public static function getAvailableClientSources(): array
    {
        return [
            self::SOURCE_SOCIAL => 'Соц сети',
            self::SOURCE_OUTDOOR => 'Наружная реклама',
            self::SOURCE_RECOMMENDATION => 'Рекомендация',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id',
                'branch_id',
                'manager_id',
                'master_id',
                'service_type',
                'status',
                'urgency',
                'client_source',
                'price',
                'internal_notes',
                'problem_description',
                'delivery_address',
                'equipment_id',
                'order_payment_type',
                'needs_delivery',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Заказ создан',
                'updated' => 'Заказ обновлен',
                'deleted' => 'Заказ удален',
                default => "Заказ {$eventName}",
            });
    }

    /**
     * Генерирует уникальный номер заказа в формате ORD-XXXXXX (6 символов)
     * Короткий уникальный хеш — без привязки к дате, поиск по номеру даёт один результат
     */
    public static function generateOrderNumber(): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $suffix = strtoupper(Str::random(6));
            $orderNumber = 'ORD-' . $suffix;

            if (!static::where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }

        throw new \RuntimeException('Не удалось сгенерировать уникальный номер заказа');
    }

    /**
     * Boot метод для автоматической генерации номера заказа
     * Убрано из события creating, теперь номер генерируется в контроллере в транзакции
     */
    protected static function boot()
    {
        parent::boot();
        // Генерация номера перенесена в контроллер для предотвращения race condition
    }
}
