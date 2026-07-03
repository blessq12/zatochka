<?php

namespace App\Application\Company\QueryHandler;

use App\Application\Company\Query\GetPublicSiteContentQuery;
use App\Domain\Company\Repository\SiteContentRepositoryInterface;

final class GetPublicSiteContentQueryHandler
{
    private const SETTING_KEYS = [
        'contacts',
        'schedule',
        'company',
        'delivery_info',
        'faq',
    ];

    public function __construct(
        private SiteContentRepositoryInterface $siteContent,
    ) {}

    /**
     * @return array{contacts: array, schedule: array, company: array, delivery_info: array, faq: array}
     */
    public function handle(GetPublicSiteContentQuery $query): array
    {
        $settings = $this->siteContent->getValuesByKeys(self::SETTING_KEYS);

        return [
            'contacts' => $settings['contacts'] ?? [],
            'schedule' => $settings['schedule'] ?? [],
            'company' => $settings['company'] ?? [],
            'delivery_info' => $settings['delivery_info'] ?? [],
            'faq' => $settings['faq'] ?? [],
        ];
    }
}
