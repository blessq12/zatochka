<?php

namespace App\Infrastructure\Warehouse\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderIssueLineModel extends Model
{
    protected $table = 'order_issue_lines';

    protected $fillable = [
        'issue_id',
        'stock_item_id',
        'qty',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(OrderIssueModel::class, 'issue_id');
    }
}
