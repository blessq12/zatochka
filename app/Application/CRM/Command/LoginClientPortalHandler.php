<?php

namespace App\Application\CRM\Command;

use App\Application\CRM\Port\ClientPortalTokenIssuer;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Phone;

final readonly class LoginClientPortalHandler
{
    public function __construct(
        private ClientRepository $clients,
        private PasswordHasher $passwords,
        private ClientPortalTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, clientId: int}
     */
    public function handle(LoginClientPortalCommand $command): array
    {
        $client = $this->clients->findByPhone(new Phone($command->phone));

        if ($client === null || ! $client->hasPortalPassword()) {
            throw new DomainException('Invalid credentials.');
        }

        if (! $this->passwords->check($command->password, (string) $client->passwordHash())) {
            throw new DomainException('Invalid credentials.');
        }

        $clientId = $client->id()->value;

        return [
            'token' => $this->tokens->issueToken($clientId),
            'clientId' => $clientId,
        ];
    }
}
