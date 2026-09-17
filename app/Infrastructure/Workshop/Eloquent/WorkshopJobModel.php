<?php

namespace App\Infrastructure\Workshop\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkshopJobModel extends Model
{
    protected $table = 'workshop_jobs';

    protected $fillable = [
        'order_id',
        'master_id',
        'status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(WorkshopItemWorkModel::class, 'job_id')->orderBy('id');
    }
}
