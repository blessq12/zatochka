<?php

namespace App\Application\PublicSite\QueryHandler;

use App\Application\Company\Query\GetPublicSiteContentQuery;
use App\Application\Company\QueryHandler\GetPublicSiteContentQueryHandler;
use App\Application\Pricing\Query\GetPublicPriceListQuery;
use App\Application\Pricing\QueryHandler\GetPublicPriceListQueryHandler;
use App\Application\PublicSite\Query\GetPublicBootstrapQuery;

final class GetPublicBootstrapQueryHandler
{
    public function __construct(
        private GetPublicSiteContentQueryHandler $siteContent,
        private GetPublicPriceListQueryHandler $priceList,
    ) {}

    /**
     * @return array{prices: list<array<string, mixed>>, contacts: array, schedule: array, delivery_info: array, company: array, faq: array}
     */
    public function handle(GetPublicBootstrapQuery $query): array
    {
        $content = $this->siteContent->handle(new GetPublicSiteContentQuery);

        return [
            'prices' => $this->priceList->handle(new GetPublicPriceListQuery),
            'contacts' => $content['contacts'],
            'schedule' => $content['schedule'],
            'delivery_info' => $content['delivery_info'],
            'company' => $content['company'],
            'faq' => $content['faq'],
        ];
    }
}
