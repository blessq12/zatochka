<?php

namespace App\Domain\Workshop;

enum WorkshopJobStatus: string
{
    case Open = 'open';
    case Completed = 'completed';
}
