<?php

namespace App\Application\Catalog\CommandHandler;

use App\Application\Catalog\Command\SaveBranchCommand;
use App\Domain\Catalog\Entity\Branch;
use App\Domain\Catalog\Repository\BranchRepositoryInterface;
use App\Domain\Catalog\Exception\BranchNotFoundException;

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
