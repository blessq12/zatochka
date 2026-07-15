<?php

namespace App\Infrastructure\Workshop\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ProductionTaskModel extends Model
{
    protected $table = 'production_tasks';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'order_item_id', 'status', 'master_id'];

    public function diagnosis(): HasOne
    {
        return $this->hasOne(DiagnosisModel::class, 'production_task_id');
    }

    public function workExecution(): HasOne
    {
        return $this->hasOne(WorkExecutionModel::class, 'production_task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MasterCommentModel::class, 'production_task_id');
    }
}
