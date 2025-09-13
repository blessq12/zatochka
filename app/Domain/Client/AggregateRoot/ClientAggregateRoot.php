<?php

namespace App\Domain\Client\AggregateRoot;

use App\Domain\Client\Event\ClientCreated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class ClientAggregateRoot extends AggregateRoot
{
    public function createClient(int $clientId): self
    {
        $this->recordThat(new ClientCreated(
            clientId: $clientId
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
