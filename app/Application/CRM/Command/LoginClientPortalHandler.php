<?php

namespace App\Application\CRM\Command;

use App\Domain\CRM\Repository\ClientRepository;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Phone;
use Illuminate\Support\Facades\Hash;

final readonly class LoginClientPortalHandler
{
    public function __construct(
        private ClientRepository $clients,
    ) {}

    /**
     * @return array{token: string, clientId: int}
     */
    public function handle(LoginClientPortalCommand $command): array
    {
        $client = $this->clients->findByPhone(new Phone($command->phone));

        if ($client === null) {
            throw new DomainException('Invalid credentials.');
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('client_id', $client->id()->value)
            ->where('role', UserRole::Client->value)
            ->first();

        if ($user === null || ! Hash::check($command->password, $user->password)) {
            throw new DomainException('Invalid credentials.');
        }

        $token = $user->createToken('client-portal')->plainTextToken;

        return [
            'token' => $token,
            'clientId' => $client->id()->value,
        ];
    }
}
