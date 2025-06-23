<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['client_id', 'order_number', 'status', 'total_amount', 'cost_price', 'profit'];

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

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
