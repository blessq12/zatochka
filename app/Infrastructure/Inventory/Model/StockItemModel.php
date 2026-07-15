<?php

namespace App\Infrastructure\Inventory\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class StockItemModel extends Model
{
    protected $table = 'stock_items';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'material_id', 'quantity_on_hand'];

    protected function casts(): array
    {
        return ['quantity_on_hand' => 'string'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(WarehouseMovementModel::class, 'stock_item_id');
    }
}
