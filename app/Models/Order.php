<?php

namespace App\Models;

use App\HasReviews;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasReviews, HasFactory;

    protected $fillable = [
        'client_id',
        'order_number',
        'service_type',
        'tool_type',
        'problem_description',
        'work_description',
        'tools_photos',
        'needs_consultation',
        'total_tools_count',
        'needs_delivery',
        'delivery_address',
        'equipment_name',
        'urgency',
        'is_paid',
        'is_ready_for_pickup',
        'quality_survey_sent',
        'review_request_sent',
        'telegram_notification_sent',
        'telegram_notification_sent_at',
        'ready_at',
        'paid_at',
        'status',
        'payment_type',
        'delivery_type',
        'total_amount',
        'cost_price',
        'profit',
        'discount_percent',
        'discount_amount',
        'final_price',
        'used_materials'
    ];

    protected $casts = [
        'tools_photos' => 'array',
        'used_materials' => 'array',
        'needs_consultation' => 'boolean',
        'needs_delivery' => 'boolean',
        'total_tools_count' => 'integer',
        'tool_type' => 'string',
        'is_paid' => 'boolean',
        'is_ready_for_pickup' => 'boolean',
        'quality_survey_sent' => 'boolean',
        'review_request_sent' => 'boolean',
        'telegram_notification_sent' => 'boolean',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'ready_at' => 'datetime',
        'paid_at' => 'datetime',
        'telegram_notification_sent_at' => 'datetime',
    ];


    protected static function booted()
    {
        static::created(function ($order) {
            event(new OrderCreated($order));
        });
        static::updated(function ($order) {
            event(new OrderStatusChanged($order, $order->getOriginal('status'), $order->status));
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function orderTools()
    {
        return $this->hasMany(OrderTool::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function bonusTransactions()
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function calculateBonusEarnAmount(): float
    {
        $minOrderAmount = BonusSetting::getFloat('min_order_amount_for_bonus', 1500);
        $bonusPercent = BonusSetting::getFloat('bonus_percent_per_order', 5);

        if ($this->final_price < $minOrderAmount) {
            return 0;
        }

        return round($this->final_price * ($bonusPercent / 100), 2);
    }

    public function canEarnBonus(): bool
    {
        return $this->calculateBonusEarnAmount() > 0;
    }

    /**
     * Отношения к типам
     */
    public function serviceType()
    {
        return $this->belongsTo(\App\Models\Types\ServiceType::class, 'service_type', 'slug');
    }

    public function paymentType()
    {
        return $this->belongsTo(\App\Models\Types\PaymentType::class, 'payment_type', 'slug');
    }

    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Types\DeliveryType::class, 'delivery_type', 'slug');
    }

    public function orderStatus()
    {
        return $this->belongsTo(\App\Models\Types\OrderStatus::class, 'status', 'slug');
    }



    // Методы для работы с фото инструментов
    public function addToolPhoto($photoPath)
    {
        $photos = $this->tools_photos ?? [];
        $photos[] = $photoPath;
        $this->update(['tools_photos' => $photos]);
    }

    public function removeToolPhoto($photoPath)
    {
        $photos = $this->tools_photos ?? [];
        $photos = array_filter($photos, fn($path) => $path !== $photoPath);
        $this->update(['tools_photos' => array_values($photos)]);
    }

    public function getToolPhotosAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    // Правила валидации для создания заказа
    public static function getValidationRules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'service_type' => 'required|in:repair,maintenance,consultation,other',
            'tools_photos' => 'nullable|array',
            'tools_photos.*' => 'nullable|string|max:255',
            'needs_consultation' => 'required|boolean',
            'total_tools_count' => 'required|integer|min:1',
        ];
    }

    // Правила валидации для обновления заказа
    public static function getUpdateValidationRules()
    {
        return [
            'service_type' => 'sometimes|required|in:repair,maintenance,consultation,other',
            'tools_photos' => 'sometimes|nullable|array',
            'tools_photos.*' => 'nullable|string|max:255',
            'needs_consultation' => 'sometimes|required|boolean',
            'total_tools_count' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:new,in_progress,completed,cancelled',
        ];
    }

    // Генерация номера заказа
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Отправка уведомлений о подтверждении заказа


    // Workflow методы для обработки заказа
    public static function getStatusOptions(): array
    {
        return [
            'new' => 'Новый',
            'confirmed' => 'Подтвержден',
            'courier_pickup' => 'Передан курьеру (забор)',
            'master_received' => 'Передан мастеру',
            'in_progress' => 'В работе',
            'work_completed' => 'Работа завершена',
            'courier_delivery' => 'Передан курьеру (доставка)',
            'ready_for_pickup' => 'Готов к выдаче',
            'delivered' => 'Доставлен',
            'payment_received' => 'Оплачен',
            'closed' => 'Закрыт',
            'cancelled' => 'Отменен',
        ];
    }

    public function confirm(): bool
    {
        if ($this->status === 'new') {
            $this->update([
                'status' => 'confirmed',
                'confirmed_at' => now()
            ]);

            return true;
        }
        return false;
    }

    public function assignToCourier(): bool
    {
        if (in_array($this->status, ['confirmed', 'new'])) {
            $this->update([
                'status' => 'courier_pickup'
            ]);
            return true;
        }
        return false;
    }

    public function assignToMaster(): bool
    {
        if (in_array($this->status, ['courier_pickup', 'confirmed'])) {
            $this->update([
                'status' => 'master_received'
            ]);
            return true;
        }
        return false;
    }

    public function startWork(): bool
    {
        if (in_array($this->status, ['master_received', 'confirmed'])) {
            $this->update(['status' => 'in_progress']);
            return true;
        }
        return false;
    }

    public function completeWork(): bool
    {
        if (in_array($this->status, ['in_progress', 'master_received'])) {
            $this->update([
                'status' => 'work_completed'
            ]);
            return true;
        }
        return false;
    }

    public function assignToDeliveryCourier(): bool
    {
        if (in_array($this->status, ['work_completed', 'ready_for_pickup'])) {
            $this->update([
                'status' => 'courier_delivery'
            ]);
            return true;
        }
        return false;
    }

    public function markAsReady(): bool
    {
        if (in_array($this->status, ['work_completed', 'in_progress'])) {
            $this->update([
                'status' => 'ready_for_pickup',
                'ready_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function markAsDelivered(): bool
    {
        if (in_array($this->status, ['courier_delivery', 'ready_for_pickup'])) {
            $this->update(['status' => 'delivered']);
            return true;
        }
        return false;
    }

    public function receivePayment(): bool
    {
        if (in_array($this->status, ['delivered', 'ready_for_pickup'])) {
            $this->update([
                'status' => 'payment_received',
                'is_paid' => true,
                'paid_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function close(): bool
    {
        if (in_array($this->status, ['payment_received', 'delivered'])) {
            $this->update([
                'status' => 'closed'
            ]);
            return true;
        }
        return false;
    }



    public function cancel(): bool
    {
        if (!in_array($this->status, ['closed', 'cancelled'])) {
            $this->update(['status' => 'cancelled']);
            return true;
        }
        return false;
    }

    // Получить следующий возможный статус
    public function getNextPossibleStatuses(): array
    {
        return match ($this->status) {
            'new' => ['confirmed', 'courier_pickup', 'cancelled'],
            'confirmed' => ['courier_pickup', 'master_received', 'cancelled'],
            'courier_pickup' => ['master_received', 'cancelled'],
            'master_received' => ['in_progress', 'work_completed', 'cancelled'],
            'in_progress' => ['work_completed', 'ready_for_pickup', 'cancelled'],
            'work_completed' => ['courier_delivery', 'ready_for_pickup', 'cancelled'],
            'courier_delivery' => ['delivered', 'cancelled'],
            'ready_for_pickup' => ['delivered', 'courier_delivery', 'cancelled'],
            'delivered' => ['payment_received', 'closed', 'cancelled'],
            'payment_received' => ['closed', 'cancelled'],
            'closed' => [],
            'cancelled' => [],
            default => [],
        };
    }

    // Получить цвет статуса для отображения
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'new' => 'gray',
            'confirmed' => 'info',
            'courier_pickup' => 'warning',
            'master_received' => 'warning',
            'in_progress' => 'warning',
            'work_completed' => 'success',
            'courier_delivery' => 'warning',
            'ready_for_pickup' => 'success',
            'delivered' => 'info',
            'payment_received' => 'success',
            'closed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    // Методы для получения опций типов
    public static function getServiceTypeOptions(): array
    {
        return \App\Models\Types\ServiceType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->toArray();
    }

    public static function getPaymentTypeOptions(): array
    {
        return \App\Models\Types\PaymentType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->toArray();
    }

    public static function getDeliveryTypeOptions(): array
    {
        return \App\Models\Types\DeliveryType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->toArray();
    }

    public static function getEquipmentTypeOptions(): array
    {
        return \App\Models\Types\EquipmentType::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->toArray();
    }
}
