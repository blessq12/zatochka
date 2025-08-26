<?php

namespace App\Events\Bonus;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BonusSpent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Client $client;
    public float $amount;
    public ?Order $order;
    public string $reason;

    public function __construct(Client $client, float $amount, string $reason, ?Order $order = null)
    {
        $this->client = $client;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->order = $order;
    }
}
