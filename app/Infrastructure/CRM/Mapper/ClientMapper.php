<?php

namespace App\Infrastructure\CRM\Mapper;

use App\Application\CRM\DTO\ClientDTO;
use App\Domain\CRM\Entity\BonusAccount;
use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Entity\ClientHistoryEntry;
use App\Infrastructure\CRM\Model\ClientHistoryModel;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\ValueObject\Email;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;
use DateTimeImmutable;

final class ClientMapper
{
    public function toDomain(ClientModel $model): Client
    {
        $history = [];

        foreach ($model->history as $row) {
            $history[] = new ClientHistoryEntry(
                new EntityId((int) $row->id),
                new OrderId((string) $row->order_id),
                (string) $row->note,
                DateTimeImmutable::createFromInterface($row->recorded_at),
            );
        }

        return Client::reconstitute(
            new EntityId((int) $model->id),
            new Phone((string) $model->phone),
            new BonusAccount(
                new EntityId((int) $model->bonus_account_id),
                (string) $model->bonus_balance,
            ),
            $model->name !== null ? (string) $model->name : null,
            $model->email !== null ? new Email((string) $model->email) : null,
            $history,
            $model->birth_date !== null
                ? ($model->birth_date instanceof \DateTimeInterface
                    ? $model->birth_date->format('Y-m-d')
                    : (string) $model->birth_date)
                : null,
            $model->delivery_address !== null ? (string) $model->delivery_address : null,
        );
    }

    public function toPersistence(Client $client, ?ClientModel $model = null): ClientModel
    {
        $model ??= new ClientModel();
        $model->id = $client->id()->value;
        $model->phone = $client->phone()->value;
        $model->name = $client->name();
        $model->email = $client->email()?->value;
        $model->birth_date = $client->birthDate();
        $model->delivery_address = $client->deliveryAddress();
        $model->bonus_account_id = $client->bonusAccount()->id()->value;
        $model->bonus_balance = $client->bonusAccount()->balance();

        return $model;
    }

    /** @return list<ClientHistoryModel> */
    public function historyToPersistence(Client $client): array
    {
        $rows = [];

        foreach ($client->history() as $entry) {
            $row = new ClientHistoryModel();
            $row->id = $entry->id->value;
            $row->client_id = $client->id()->value;
            $row->order_id = $entry->orderId->value;
            $row->note = $entry->note;
            $row->recorded_at = $entry->recordedAt;
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(ClientModel $model): ClientDTO
    {
        return new ClientDTO(
            (int) $model->id,
            (string) $model->phone,
            $model->name !== null ? (string) $model->name : null,
            $model->email !== null ? (string) $model->email : null,
            (string) $model->bonus_balance,
            $model->birth_date !== null
                ? ($model->birth_date instanceof \DateTimeInterface
                    ? $model->birth_date->format('Y-m-d')
                    : (string) $model->birth_date)
                : null,
            $model->delivery_address !== null ? (string) $model->delivery_address : null,
        );
    }
}
