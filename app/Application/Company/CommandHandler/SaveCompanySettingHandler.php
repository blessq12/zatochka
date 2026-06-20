<?php

namespace App\Application\Company\CommandHandler;

use App\Application\Company\Command\SaveCompanySettingCommand;
use App\Domain\Company\Entity\CompanySetting;
use App\Domain\Company\Repository\CompanySettingRepositoryInterface;

final class SaveCompanySettingHandler
{
    public function __construct(
        private CompanySettingRepositoryInterface $companySettings,
    ) {}

    public function handle(SaveCompanySettingCommand $command): CompanySetting
    {
        $existing = $this->companySettings->findByKey($command->key);

        return $this->companySettings->save(new CompanySetting(
            id: $existing?->id(),
            key: $command->key,
            value: $command->value,
        ));
    }
}
