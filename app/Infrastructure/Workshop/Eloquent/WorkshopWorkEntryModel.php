<?php

namespace App\Infrastructure\Workshop\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkshopWorkEntryModel extends Model
{
    protected $table = 'workshop_work_entries';

    protected $fillable = [
        'item_work_id',
        'title',
        'position',
        'equipment_module_id',
    ];

    public function itemWork(): BelongsTo
    {
        return $this->belongsTo(WorkshopItemWorkModel::class, 'item_work_id');
    }
}
