<?php

namespace App\Domain\Order\VO;

enum OrderServiceType: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';
}
