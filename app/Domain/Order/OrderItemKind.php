<?php

namespace App\Domain\Order;

enum OrderItemKind: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';
}
