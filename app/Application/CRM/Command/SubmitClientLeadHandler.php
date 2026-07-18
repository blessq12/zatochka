<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Repository\ClientRepository;
use App\Infrastructure\CRM\Model\ClientLeadModel;
use App\Shared\ValueObject\Phone;

final readonly class SubmitClientLeadHandler
{
    public function __construct(
        private RegisterClientHandler $registerClient,
        private ClientRepository $clients,
        private EntityIdGenerator $ids,
        private UnitOfWork $unitOfWork,
    ) {}

    /**
     * @return array{message: string, client_id: int, lead_id: int}
     */
    public function handle(SubmitClientLeadCommand $command): array
    {
        return $this->unitOfWork->execute(function () use ($command): array {
            $phone = new Phone($command->phone);
            $client = $this->clients->findByPhone($phone);

            if ($client === null) {
                $clientId = $this->ids->next('client')->value;
                $this->registerClient->handle(new RegisterClientCommand(
                    $clientId,
                    $this->ids->next('bonus_account')->value,
                    $command->phone,
                    $command->fullName !== '' ? $command->fullName : null,
                    $command->email,
                ));
            } else {
                $clientId = $client->id()->value;
            }

            $leadId = $this->ids->next('client_lead')->value;

            ClientLeadModel::query()->create([
                'id' => $leadId,
                'client_id' => $clientId,
                'service_types' => $command->serviceTypes,
                'comment' => $command->comment,
                'intake_data' => $command->intakeData,
                'needs_delivery' => $command->needsDelivery,
                'delivery_address' => $command->deliveryAddress,
                'status' => 'new',
            ]);

            return [
                'message' => 'Заявка принята. Менеджер свяжется с вами.',
                'client_id' => $clientId,
                'lead_id' => $leadId,
            ];
        });
    }
}
