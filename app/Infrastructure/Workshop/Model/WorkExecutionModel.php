<?php

namespace App\Infrastructure\Workshop\Model;

use Illuminate\Database\Eloquent\Model;

final class WorkExecutionModel extends Model
{
    protected $table = 'work_executions';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'production_task_id',
        'description',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
