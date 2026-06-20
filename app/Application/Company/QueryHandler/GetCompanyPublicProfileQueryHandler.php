<?php

namespace App\Application\Company\QueryHandler;

use App\Application\Company\Query\GetCompanyPublicProfileQuery;
use App\Domain\Company\Repository\CompanySettingRepositoryInterface;

final class GetCompanyPublicProfileQueryHandler
{
    private const SETTING_KEYS = ['contacts', 'schedule', 'company'];

    public function __construct(
        private CompanySettingRepositoryInterface $companySettings,
    ) {}

    /**
     * @return array{contacts: array, schedule: array, company: array}
     */
    public function handle(GetCompanyPublicProfileQuery $query): array
    {
        $settings = $this->companySettings->getValuesByKeys(self::SETTING_KEYS);

        return [
            'contacts' => $settings['contacts'] ?? [],
            'schedule' => $settings['schedule'] ?? [],
            'company' => $settings['company'] ?? [],
        ];
    }
}
