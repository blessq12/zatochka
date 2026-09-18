<?php

namespace App\Infrastructure\Warehouse\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderIssueModel extends Model
{
    protected $table = 'order_issues';

    protected $fillable = [
        'order_id',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(OrderIssueLineModel::class, 'issue_id')->orderBy('id');
    }
}
