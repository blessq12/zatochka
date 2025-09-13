<?php

namespace App\Reactors;

use App\Domain\Client\Event\ClientCreated;
use App\Application\UseCases\Bonus\CreateBonusAccountUseCase;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Illuminate\Support\Facades\Log;

class ClientReactor extends Reactor
{
    public function onClientCreated(ClientCreated $event): void
    {
        try {
            $bonusAccount = app(CreateBonusAccountUseCase::class)
                ->loadData(['client_id' => $event->clientId])
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            Log::error('Failed to create bonus account for client', [
                'client_id' => $event->clientId,
                'error' => $e->getMessage()
            ]);
        }

        // TODO: Отправка приветственного сообщения
        // TODO: Регистрация в Telegram боте
    }
}
