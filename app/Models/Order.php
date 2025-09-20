<?php

namespace App\Models;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderUrgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'branch_id',
        'manager_id',
        'master_id',
        'order_number',
        'type',
        'status',
        'urgency',
        'is_paid',
        'paid_at',
        'discount_id',
        'total_amount',
        'final_price',
        'cost_price',
        'profit',
        'internal_notes',
        'problem_description',
        'is_deleted',
    ];

    protected $casts = [
        'type' => OrderType::class,
        'status' => OrderStatus::class,
        'urgency' => OrderUrgency::class,
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'profit' => 'decimal:2',
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

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }



    public function discount()
    {
        return $this->belongsTo(DiscountRule::class, 'discount_id');
    }


    public function repairs()
    {
        return $this->hasMany(Repair::class);
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

    public function tools()
    {
        return $this->belongsToMany(Tool::class, 'order_tools')
            ->withPivot(['problem_description', 'work_description'])
            ->withTimestamps();
    }

    // Scope для активных заказов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для заказов по статусу
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
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
        return $this->status === OrderStatus::NEW;
    }

    public function isInWork(): bool
    {
        return $this->status === OrderStatus::IN_WORK;
    }

    public function isReady(): bool
    {
        return $this->status === OrderStatus::READY;
    }

    public function isIssued(): bool
    {
        return $this->status === OrderStatus::ISSUED;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function isManagerStatus(): bool
    {
        return $this->status->isManagerStatus();
    }

    public function isWorkshopStatus(): bool
    {
        return $this->status->isWorkshopStatus();
    }

    public function canTransferToWorkshop(): bool
    {
        return app(\App\Domain\Order\Service\OrderStatusGroupingService::class)
            ->canTransferToWorkshop($this->status);
    }

    public function canTransferToManager(): bool
    {
        return app(\App\Domain\Order\Service\OrderStatusGroupingService::class)
            ->canTransferToManager($this->status);
    }

    public function getAvailableTransitions(): array
    {
        return app(\App\Domain\Order\Service\OrderStatusGroupingService::class)
            ->getAvailableTransitions($this->status);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id',
                'branch_id',
                'manager_id',
                'master_id',
                'type',
                'status',
                'urgency',
                'is_paid',
                'paid_at',
                'total_amount',
                'final_price',
                'cost_price',
                'profit',
                'internal_notes',
                'problem_description',
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
}
