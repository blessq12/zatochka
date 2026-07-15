<?php

namespace App\Http\Controllers\CRM;

use App\Application\CRM\Command\AccrueBonusCommand;
use App\Application\CRM\Command\AccrueBonusHandler;
use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Application\CRM\Command\UpdateClientCommand;
use App\Application\CRM\Command\UpdateClientHandler;
use App\Application\CRM\Query\GetClientByIdHandler;
use App\Application\CRM\Query\GetClientByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientController extends Controller
{
    public function __construct(
        private RegisterClientHandler $registerClient,
        private UpdateClientHandler $updateClient,
        private AccrueBonusHandler $accrueBonus,
        private GetClientByIdHandler $getClientById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        $clientId = $this->ids->next('client')->value;
        $bonusAccountId = $this->ids->next('bonus_account')->value;

        $this->registerClient->handle(new RegisterClientCommand(
            $clientId,
            $bonusAccountId,
            $data['phone'],
            $data['name'] ?? null,
            $data['email'] ?? null,
        ));

        return $this->created($this->getClientById->handle(new GetClientByIdQuery($clientId)));
    }

    public function show(int $clientId): JsonResponse
    {
        $client = $this->getClientById->handle(new GetClientByIdQuery($clientId));

        if ($client === null) {
            return response()->json(['message' => 'Client not found.'], 404);
        }

        return $this->ok($client);
    }

    public function update(Request $request, int $clientId): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        $this->updateClient->handle(new UpdateClientCommand(
            $clientId,
            $data['name'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
        ));

        return $this->ok($this->getClientById->handle(new GetClientByIdQuery($clientId)));
    }

    public function accrueBonus(Request $request, int $clientId): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->accrueBonus->handle(new AccrueBonusCommand($clientId, (string) $data['amount']));

        return $this->ok($this->getClientById->handle(new GetClientByIdQuery($clientId)));
    }
}
