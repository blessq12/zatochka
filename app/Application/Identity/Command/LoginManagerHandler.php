<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\ManagerTokenIssuer;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Shared\Domain\DomainException;

final readonly class LoginManagerHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private PasswordHasher $passwords,
        private ManagerTokenIssuer $tokens,
    ) {}

    /**
     * @return array{token: string, manager: array{id: int, name: string, email: string, role: string}}
     */
    public function handle(LoginManagerCommand $command): array
    {
        $manager = $this->managers->findByEmail($command->email);

        if ($manager === null || ! $this->passwords->check($command->plainPassword, $manager->passwordHash())) {
            throw new DomainException('Неверный email или пароль.');
        }

        return [
            'token' => $this->tokens->issueToken($manager->id()->value),
            'manager' => [
                'id' => $manager->id()->value,
                'name' => $manager->name(),
                'email' => $manager->email(),
                'role' => 'manager',
            ],
        ];
    }
}
