<?php

namespace App\Domain\Order;

enum OrderDraftSource: string
{
    case Public = 'public';
    case ClientLk = 'client_lk';
}
