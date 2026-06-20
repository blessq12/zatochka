<?php

namespace App\Application\PublicSite\QueryHandler;

use App\Application\Company\Query\GetCompanyPublicProfileQuery;
use App\Application\Company\QueryHandler\GetCompanyPublicProfileQueryHandler;
use App\Application\Pricing\Query\GetPublicPriceListQuery;
use App\Application\Pricing\QueryHandler\GetPublicPriceListQueryHandler;
use App\Application\PublicSite\Query\GetPublicBootstrapQuery;
use App\Application\SiteSettings\Query\GetSiteContentQuery;
use App\Application\SiteSettings\QueryHandler\GetSiteContentQueryHandler;

final class GetPublicBootstrapQueryHandler
{
    public function __construct(
        private GetCompanyPublicProfileQueryHandler $companyProfile,
        private GetPublicPriceListQueryHandler $priceList,
        private GetSiteContentQueryHandler $siteContent,
    ) {}

    /**
     * @return array{prices: list<array<string, mixed>>, contacts: array, schedule: array, delivery_info: array, company: array, faq: array}
     */
    public function handle(GetPublicBootstrapQuery $query): array
    {
        $profile = $this->companyProfile->handle(new GetCompanyPublicProfileQuery);
        $content = $this->siteContent->handle(new GetSiteContentQuery);

        return [
            'prices' => $this->priceList->handle(new GetPublicPriceListQuery),
            'contacts' => $profile['contacts'],
            'schedule' => $profile['schedule'],
            'delivery_info' => $content['delivery_info'],
            'company' => $profile['company'],
            'faq' => $content['faq'],
        ];
    }
}
