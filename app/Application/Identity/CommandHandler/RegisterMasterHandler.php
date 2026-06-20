<?php

namespace App\Application\Identity\CommandHandler;

use App\Application\Identity\Command\RegisterMasterCommand;
use App\Domain\Identity\Entity\Master;
use App\Domain\Identity\Exception\MasterAlreadyExistsException;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Hash;

final class RegisterMasterHandler
{
    public function __construct(
        private MasterRepositoryInterface $masters,
    ) {}

    public function handle(RegisterMasterCommand $command): Master
    {
        if ($this->masters->findByEmail($command->email) !== null) {
            throw MasterAlreadyExistsException::withEmail($command->email);
        }

        $master = $this->masters->save(new Master(
            id: null,
            name: $command->name,
            surname: $command->surname,
            email: $command->email,
            phone: $command->phone,
        ));

        $userId = $master->id();
        if ($userId === null) {
            throw new \RuntimeException('Не удалось создать пользователя.');
        }

        UserModel::query()
            ->whereKey($userId)
            ->update(['password' => Hash::make($command->password)]);

        return $master;
    }
}
