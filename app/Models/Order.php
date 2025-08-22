<?php

namespace App\Models;

use App\HasReviews;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasReviews;
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
        'is_paid',
        'is_ready_for_pickup',
        'quality_survey_sent',
        'review_request_sent',
        'ready_at',
        'paid_at',
        'status',
        'total_amount',
        'cost_price',
        'profit',
        'discount_percent',
        'discount_amount',
        'final_price',
        'used_materials',
        'confirmed_at',
        'courier_pickup_at',
        'master_received_at',
        'work_completed_at',
        'courier_delivery_at',
        'payment_received_at',
        'closed_at'
    ];

    protected $casts = [
        'tools_photos' => 'array',
        'used_materials' => 'array',
        'needs_consultation' => 'boolean',
        'total_tools_count' => 'integer',
        'tool_type' => 'string',
        'is_paid' => 'boolean',
        'is_ready_for_pickup' => 'boolean',
        'quality_survey_sent' => 'boolean',
        'review_request_sent' => 'boolean',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'ready_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'courier_pickup_at' => 'datetime',
        'master_received_at' => 'datetime',
        'work_completed_at' => 'datetime',
        'courier_delivery_at' => 'datetime',
        'payment_received_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

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
    public function sendConfirmationNotifications(): void
    {
        $telegramService = app(\App\Contracts\TelegramServiceContract::class);
        $smsService = app(\App\Contracts\SMSServiceContract::class);

        // Отправляем в Telegram
        if ($this->client->telegram) {
            $telegramSent = $telegramService->sendOrderConfirmation(
                $this->client->telegram,
                $this->order_number,
                $this->total_amount ?? 0
            );

            // Сохраняем уведомление
            $this->notifications()->create([
                'client_id' => $this->client_id,
                'type' => 'order_confirmation',
                'message_text' => "Telegram: Заявка {$this->order_number} подтверждена. Сумма: {$this->total_amount} ₽",
                'sent_at' => $telegramSent ? now() : null
            ]);
        }

        // Отправляем SMS
        if ($this->client->phone) {
            $smsSent = $smsService->sendOrderConfirmation(
                $this->client->phone,
                $this->order_number,
                $this->total_amount ?? 0
            );

            // Сохраняем уведомление
            $this->notifications()->create([
                'client_id' => $this->client_id,
                'type' => 'order_confirmation',
                'message_text' => "SMS: Заявка {$this->order_number} подтверждена. Сумма: {$this->total_amount} ₽",
                'sent_at' => $smsSent ? now() : null
            ]);
        }
    }

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
            $this->sendConfirmationNotifications();
            return true;
        }
        return false;
    }

    public function assignToCourier(): bool
    {
        if (in_array($this->status, ['confirmed', 'new'])) {
            $this->update([
                'status' => 'courier_pickup',
                'courier_pickup_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function assignToMaster(): bool
    {
        if (in_array($this->status, ['courier_pickup', 'confirmed'])) {
            $this->update([
                'status' => 'master_received',
                'master_received_at' => now()
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
                'status' => 'work_completed',
                'work_completed_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function assignToDeliveryCourier(): bool
    {
        if (in_array($this->status, ['work_completed', 'ready_for_pickup'])) {
            $this->update([
                'status' => 'courier_delivery',
                'courier_delivery_at' => now()
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
                'paid_at' => now(),
                'payment_received_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function close(): bool
    {
        if (in_array($this->status, ['payment_received', 'delivered'])) {
            $this->update([
                'status' => 'closed',
                'closed_at' => now()
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
}
