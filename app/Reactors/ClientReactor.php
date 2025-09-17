<?php

namespace App\Reactors;

use App\Domain\Client\Event\ClientCreated;
use App\Domain\Client\Event\ClientRegistered;
use App\Domain\Client\Event\ClientLoggedIn;
use App\Domain\Client\Repository\ClientRepository;
use App\Application\UseCases\Bonus\CreateBonusAccountUseCase;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Illuminate\Support\Facades\Log;

class ClientReactor extends Reactor
{
    protected ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function onClientCreated(ClientCreated $event): void
    {
        try {
            $client = $this->clientRepository->get((string) $event->clientId);

            if ($client) {
                $bonusAccount = app(CreateBonusAccountUseCase::class)
                    ->loadData(['client_id' => $client->getId()])
                    ->validate()
                    ->execute();
            } else {
                Log::warning('Client not found for bonus account creation', [
                    'client_id' => $event->clientId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create bonus account for client', [
                'client_id' => $event->clientId,
                'error' => $e->getMessage()
            ]);
        }

        // TODO: Отправка приветственного сообщения
        // TODO: Регистрация в Telegram боте
    }

    public function onClientRegistered(ClientRegistered $event): void
    {
        try {
            $client = $this->clientRepository->findByPhone($event->phone);

            if ($client) {
                $bonusAccount = app(CreateBonusAccountUseCase::class)
                    ->loadData(['client_id' => $client->getId()])
                    ->validate()
                    ->execute();
            } else {
                Log::warning('Client not found for bonus account creation', [
                    'phone' => $event->phone
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create bonus account for registered client', [
                'phone' => $event->phone,
                'error' => $e->getMessage()
            ]);
        }

        // TODO: Отправка приветственного сообщения
        // TODO: Регистрация в Telegram боте
        // TODO: Отправка email подтверждения
    }

    public function onClientLoggedIn(ClientLoggedIn $event): void
    {
        Log::info('Client logged in', [
            'phone' => $event->phone,
            'timestamp' => now()
        ]);

        // TODO: Обновление статистики входов
        // TODO: Отправка уведомления о входе (если нужно)
    }
}
