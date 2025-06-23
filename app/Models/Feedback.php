<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['client_id', 'order_id', 'feedback_text', 'rating'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
