<?php

namespace App\Application\Identity\Command;

use App\Application\CRM\Port\ClientPortalTokenIssuer;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ClientAccountRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Phone;

final readonly class LoginClientPortalHandler
{
    public function __construct(
        private ClientAccountRepository $accounts,
        private PasswordHasher $passwords,
        private ClientPortalTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, clientId: int}
     */
    public function handle(LoginClientPortalCommand $command): array
    {
        $account = $this->accounts->findByPhone(new Phone($command->phone));

        if ($account === null || ! $this->passwords->check($command->plainPassword, $account->passwordHash())) {
            throw new DomainException('Invalid credentials.');
        }

        $clientId = $account->clientId()->value;

        return [
            'token' => $this->tokens->issueToken($clientId),
            'clientId' => $clientId,
        ];
    }
}
