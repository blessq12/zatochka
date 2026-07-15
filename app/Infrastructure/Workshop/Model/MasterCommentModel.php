<?php

namespace App\Infrastructure\Workshop\Model;

use Illuminate\Database\Eloquent\Model;

final class MasterCommentModel extends Model
{
    protected $table = 'master_comments';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'production_task_id',
        'master_id',
        'text',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
