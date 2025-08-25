<?php

namespace App\Events\Review;

use App\Models\Review;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Review $review;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(Review $review, string $oldStatus, string $newStatus)
    {
        $this->review = $review;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
