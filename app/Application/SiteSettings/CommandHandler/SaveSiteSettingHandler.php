<?php

namespace App\Application\SiteSettings\CommandHandler;

use App\Application\SiteSettings\Command\SaveSiteSettingCommand;
use App\Domain\SiteSettings\Entity\SiteSetting;
use App\Domain\SiteSettings\Repository\SiteSettingRepositoryInterface;

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
