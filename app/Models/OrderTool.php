<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTool extends Model
{
    protected $fillable = ['order_id', 'tool_id', 'quantity', 'cost_price', 'profit'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
