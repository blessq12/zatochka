<?php

namespace App\Events\Bonus;

use App\Models\Client;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BonusExpiring
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Client $client;
    public float $balance;
    public int $daysLeft;

    public function __construct(Client $client, float $balance, int $daysLeft)
    {
        $this->client = $client;
        $this->balance = $balance;
        $this->daysLeft = $daysLeft;
    }
}
