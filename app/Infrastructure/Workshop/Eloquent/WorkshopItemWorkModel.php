<?php

namespace App\Infrastructure\Workshop\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkshopItemWorkModel extends Model
{
    protected $table = 'workshop_item_works';

    protected $fillable = [
        'job_id',
        'order_item_id',
        'completed_qty',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(WorkshopJobModel::class, 'job_id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(WorkshopWorkEntryModel::class, 'item_work_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}
