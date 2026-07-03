<?php

namespace App\Application\ClientPortal\Presenter;

use App\Domain\ClientPortal\Entity\Client;
use App\Domain\OrderFulfillment\Entity\Order;
use DateTimeInterface;

final class ClientProfilePresenter
{
    /** @return array<string, mixed> */
    public static function present(Client $client): array
    {
        return [
            'id' => $client->id(),
            'full_name' => $client->fullName(),
            'phone' => $client->phone(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate(),
            'delivery_address' => $client->deliveryAddress(),
            'requires_password_set' => $client->requiresPasswordSet(),
        ];
    }
}
