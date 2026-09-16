<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\ManagerTokenIssuer;
use App\Application\Identity\Port\StaffPersonReadPort;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerAccountRepository;
use App\Shared\Domain\DomainException;

final readonly class LoginManagerHandler
{
    public function __construct(
        private ManagerAccountRepository $accounts,
        private StaffPersonReadPort $people,
        private PasswordHasher $passwords,
        private ManagerTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, manager: array{id: int, name: string, email: string, role: string}}
     */
    public function handle(LoginManagerCommand $command): array
    {
        $account = $this->accounts->findByEmail($command->email);

        if ($account === null || ! $this->passwords->check($command->plainPassword, $account->passwordHash())) {
            throw new DomainException('Неверный email или пароль.');
        }

        $person = $this->people->findManager($account->id()->value);

        if ($person === null) {
            throw new DomainException('Неверный email или пароль.');
        }

        return [
            'token' => $this->tokens->issueToken($account->id()->value),
            'manager' => [
                'id' => $account->id()->value,
                'name' => $person['name'],
                'email' => $person['email'],
                'role' => 'manager',
            ],
        ];
    }
}
