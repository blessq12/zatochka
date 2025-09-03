<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'client_id',
        'branch_id',
        'manager_id',
        'master_id',
        'order_number',
        'service_type_id',
        'status_id',
        'urgency',
        'is_paid',
        'paid_at',
        'discount_id',
        'total_amount',
        'final_price',
        'cost_price',
        'profit',
        'is_deleted',
    ];

    protected $casts = [
        'urgency' => 'string',
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

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function discount()
    {
        return $this->belongsTo(DiscountRule::class, 'discount_id');
    }

    public function orderLogs()
    {
        return $this->hasMany(OrderLog::class);
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

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

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
}
