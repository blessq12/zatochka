<?php

namespace App\Models;

// Удалены Enum'ы - теперь используются строки в БД
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
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
        'discount_id',
        'estimated_price',
        'actual_price',
        'internal_notes',
        'problem_description',
        'delivery_address',
        'delivery_cost',
        'needs_delivery',
        'order_payment_type',
        'is_deleted',
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'actual_price' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
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

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function warehouse()
    {
        return $this->hasOneThrough(Warehouse::class, Branch::class, 'id', 'branch_id', 'branch_id', 'id');
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
                'master_id',
                'service_type',
                'status',
                'urgency',
                'estimated_price',
                'actual_price',
                'internal_notes',
                'problem_description',
                'delivery_address',
                'delivery_cost',
                'equipment_id',
                'order_payment_type',
                'needs_delivery',
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
     * Должен вызываться внутри транзакции!
     */
    public static function generateOrderNumber(): string
    {
        $date = date('Ymd');
        
        // Используем SELECT FOR UPDATE для блокировки всех строк с номерами за сегодня
        // Это гарантирует, что другие транзакции будут ждать
        $lastOrder = DB::table('orders')
            ->where('order_number', 'like', 'ORD-'.$date.'-%')
            ->orderBy('order_number', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastOrder && preg_match('/ORD-'.$date.'-(\d+)/', $lastOrder->order_number, $matches)) {
            $count = (int) $matches[1] + 1;
        } else {
            $count = 1;
        }

        $orderNumber = 'ORD-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Дополнительная проверка уникальности
        $exists = static::where('order_number', $orderNumber)->lockForUpdate()->exists();
        
        if ($exists) {
            // Если номер все равно существует, увеличиваем счетчик
            $count++;
            $orderNumber = 'ORD-'.$date.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        return $orderNumber;
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
