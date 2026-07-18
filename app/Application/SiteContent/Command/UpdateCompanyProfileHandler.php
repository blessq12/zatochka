<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Repository\CompanyProfileRepository;

final readonly class UpdateCompanyProfileHandler
{
    public function __construct(
        private CompanyProfileRepository $profiles,
    ) {}

    public function handle(UpdateCompanyProfileCommand $command): void
    {
        $profile = $this->profiles->get();
        $profile->update(
            $command->ownerName,
            $command->inn,
            $command->ogrn,
            $command->legalAddress,
            $command->actualAddress,
        );
        $this->profiles->save($profile);
    }
}
