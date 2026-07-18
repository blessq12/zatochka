<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Repository\ClientRepository;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Phone;

final readonly class RegisterClientPortalHandler
{
    public function __construct(
        private RegisterClientHandler $registerClient,
        private ClientRepository $clients,
        private EntityIdGenerator $ids,
        private UnitOfWork $unitOfWork,
    ) {}

    /**
     * @return array{token: string, clientId: int}
     */
    public function handle(RegisterClientPortalCommand $command): array
    {
        return $this->unitOfWork->execute(function () use ($command): array {
            $phone = new Phone($command->phone);

            if ($this->clients->findByPhone($phone) !== null) {
                throw new DomainException('Client with this phone already exists.');
            }

            if (User::query()->where('email', $command->email)->exists()) {
                throw new DomainException('User with this email already exists.');
            }

            $clientId = $this->ids->next('client')->value;
            $bonusAccountId = $this->ids->next('bonus_account')->value;

            $this->registerClient->handle(new RegisterClientCommand(
                $clientId,
                $bonusAccountId,
                $command->phone,
                $command->fullName,
                $command->email,
            ));

            $user = User::query()->create([
                'name' => $command->fullName,
                'email' => $command->email,
                'password' => $command->password,
                'role' => UserRole::Client,
                'client_id' => $clientId,
                'requires_password_set' => false,
            ]);

            $token = $user->createToken('client-portal')->plainTextToken;

            return [
                'token' => $token,
                'clientId' => $clientId,
            ];
        });
    }
}
