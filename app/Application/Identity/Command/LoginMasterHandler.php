<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\MasterTokenIssuer;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\Domain\DomainException;

final readonly class LoginMasterHandler
{
    public function __construct(
        private MasterRepository $masters,
        private PasswordHasher $passwords,
        private MasterTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, master: array{id: int, name: string, email: string, role: string}}
     */
    public function handle(LoginMasterCommand $command): array
    {
        $master = $this->masters->findByEmail($command->email);

        if ($master === null || ! $this->passwords->check($command->plainPassword, $master->passwordHash())) {
            throw new DomainException('Неверный email или пароль.');
        }

        return [
            'token' => $this->tokens->issueToken($master->id()->value),
            'master' => [
                'id' => $master->id()->value,
                'name' => $master->name(),
                'email' => $master->email(),
                'role' => 'master',
            ],
        ];
    }
}
