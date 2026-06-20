<?php

namespace App\Application\SiteSettings\QueryHandler;

use App\Application\SiteSettings\Query\GetSiteContentQuery;
use App\Domain\SiteSettings\Repository\SiteSettingRepositoryInterface;

final class GetSiteContentQueryHandler
{
    private const SETTING_KEYS = ['delivery_info', 'faq'];

    public function __construct(
        private SiteSettingRepositoryInterface $siteSettings,
    ) {}

    /**
     * @return array{delivery_info: array, faq: array}
     */
    public function handle(GetSiteContentQuery $query): array
    {
        $settings = $this->siteSettings->getValuesByKeys(self::SETTING_KEYS);

        return [
            'delivery_info' => $settings['delivery_info'] ?? [],
            'faq' => $settings['faq'] ?? [],
        ];
    }
}
