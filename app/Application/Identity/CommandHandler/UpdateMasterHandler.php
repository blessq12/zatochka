<?php

namespace App\Application\Identity\CommandHandler;

use App\Application\Identity\Command\UpdateMasterCommand;
use App\Domain\Identity\Entity\Master;
use App\Domain\Identity\Exception\MasterAlreadyExistsException;
use App\Domain\Identity\Exception\MasterNotFoundException;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Hash;

final class UpdateMasterHandler
{
    public function __construct(
        private MasterRepositoryInterface $masters,
    ) {}

    public function handle(UpdateMasterCommand $command): Master
    {
        $existing = $this->masters->findById($command->id);

        if ($existing === null) {
            throw MasterNotFoundException::withId($command->id);
        }

        $emailOwner = $this->masters->findByEmail($command->email);
        if ($emailOwner !== null && $emailOwner->id() !== $command->id) {
            throw MasterAlreadyExistsException::withEmail($command->email);
        }

        $master = $this->masters->save(new Master(
            id: $command->id,
            name: $command->name,
            surname: $command->surname,
            email: $command->email,
            phone: $command->phone,
            notificationsEnabled: $command->notificationsEnabled,
        ));

        if ($command->password !== null && $command->password !== '') {
            UserModel::query()
                ->whereKey($command->id)
                ->update(['password' => Hash::make($command->password)]);
        }

        return $master;
    }
}
