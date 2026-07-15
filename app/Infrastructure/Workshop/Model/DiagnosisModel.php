<?php

namespace App\Infrastructure\Workshop\Model;

use Illuminate\Database\Eloquent\Model;

final class DiagnosisModel extends Model
{
    protected $table = 'diagnoses';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'production_task_id',
        'summary',
        'technical_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
