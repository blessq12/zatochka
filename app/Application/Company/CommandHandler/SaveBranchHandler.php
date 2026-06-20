<?php

namespace App\Application\Company\CommandHandler;

use App\Application\Company\Command\SaveBranchCommand;
use App\Domain\Company\Entity\Branch;
use App\Domain\Company\Exception\BranchNotFoundException;
use App\Domain\Company\Repository\BranchRepositoryInterface;

final class SaveBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branches,
    ) {}

    public function handle(SaveBranchCommand $command): Branch
    {
        $existing = $this->branches->findById($command->id);

        if ($existing === null) {
            throw new BranchNotFoundException($command->id);
        }

        return $this->branches->save(new Branch(
            id: $command->id,
            name: $command->name,
            address: $command->address,
            phone: $command->phone,
            isActive: $command->isActive,
        ));
    }
}
