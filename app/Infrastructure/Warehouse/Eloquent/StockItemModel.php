<?php

namespace App\Infrastructure\Warehouse\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class StockItemModel extends Model
{
    protected $table = 'stock_items';

    protected $fillable = [
        'category',
        'name',
        'qty_on_hand',
        'unit',
    ];
}
