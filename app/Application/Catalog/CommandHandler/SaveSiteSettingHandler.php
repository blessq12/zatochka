<?php

namespace App\Application\Catalog\CommandHandler;

use App\Application\Catalog\Command\SaveSiteSettingCommand;
use App\Domain\Catalog\Entity\SiteSetting;
use App\Domain\Catalog\Repository\SiteSettingRepositoryInterface;

final class SaveSiteSettingHandler
{
    public function __construct(
        private SiteSettingRepositoryInterface $siteSettings,
    ) {}

    public function handle(SaveSiteSettingCommand $command): SiteSetting
    {
        $existing = $this->siteSettings->findByKey($command->key);

        return $this->siteSettings->save(new SiteSetting(
            id: $existing?->id(),
            key: $command->key,
            value: $command->value,
        ));
    }
}
