<?php

namespace App\Application\CRM\Command;

use App\Application\CRM\Port\ClientPortalTokenIssuer;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\Port\PasswordHasher;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\Phone;

final readonly class RegisterClientPortalHandler
{
    public function __construct(
        private RegisterClientHandler $registerClient,
        private ClientRepository $clients,
        private EntityIdGenerator $ids,
        private PasswordHasher $passwords,
        private ClientPortalTokenIssuer $tokens,
        private UnitOfWork $unitOfWork,
    ) {}

    /**
     * @return array{token: string, clientId: int}
     */
    public function handle(RegisterClientPortalCommand $command): array
    {
        return $this->unitOfWork->execute(function () use ($command): array {
            $phone = new Phone($command->phone);
            $email = new Email($command->email);

            if ($this->clients->findByPhone($phone) !== null) {
                throw new DomainException('Client with this phone already exists.');
            }

            if ($this->clients->findByEmail($email) !== null) {
                throw new DomainException('Client with this email already exists.');
            }

            $clientId = $this->ids->next('client')->value;
            $bonusAccountId = $this->ids->next('bonus_account')->value;

            $this->registerClient->handle(new RegisterClientCommand(
                $clientId,
                $bonusAccountId,
                $command->phone,
                $command->fullName,
                $command->email,
                passwordHash: $this->passwords->hash($command->password),
            ));

            return [
                'token' => $this->tokens->issueToken($clientId),
                'clientId' => $clientId,
            ];
        });
    }
}
