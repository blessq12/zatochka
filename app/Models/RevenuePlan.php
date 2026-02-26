<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenuePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'target_amount',
        'branch_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'target_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        $monthName = now()
            ->setDate($this->year, $this->month, 1)
            ->translatedFormat('F');

        return "{$monthName} {$this->year}";
    }
}

