<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\MasterTokenIssuer;
use App\Application\Identity\Port\StaffPersonReadPort;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\MasterAccountRepository;
use App\Shared\Domain\DomainException;

final readonly class LoginMasterHandler
{
    public function __construct(
        private MasterAccountRepository $accounts,
        private StaffPersonReadPort $people,
        private PasswordHasher $passwords,
        private MasterTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, master: array{id: int, name: string, email: string, role: string}}
     */
    public function handle(LoginMasterCommand $command): array
    {
        $account = $this->accounts->findByEmail($command->email);

        if ($account === null || ! $this->passwords->check($command->plainPassword, $account->passwordHash())) {
            throw new DomainException('Неверный email или пароль.');
        }

        $person = $this->people->findMaster($account->id()->value);

        if ($person === null) {
            throw new DomainException('Неверный email или пароль.');
        }

        return [
            'token' => $this->tokens->issueToken($account->id()->value),
            'master' => [
                'id' => $account->id()->value,
                'name' => $person['name'],
                'email' => $person['email'],
                'role' => 'master',
            ],
        ];
    }
}
