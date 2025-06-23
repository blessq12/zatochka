<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $fillable = ['name', 'quantity', 'cost_price', 'status'];

    public function orderTools()
    {
        return $this->hasMany(OrderTool::class);
    }
}
