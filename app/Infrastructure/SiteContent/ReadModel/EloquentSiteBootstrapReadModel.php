<?php

namespace App\Infrastructure\SiteContent\ReadModel;

use App\Application\SiteContent\ReadPort\SiteBootstrapReadPort;
use App\Domain\SiteContent\Entity\CompanyProfile;
use App\Domain\SiteContent\Entity\DeliveryInfo;
use App\Domain\SiteContent\Entity\SiteContacts;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use App\Infrastructure\SiteContent\Model\ServicePriceModel;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use App\Shared\Domain\DomainException;

final class EloquentSiteBootstrapReadModel implements SiteBootstrapReadPort
{
    public function getBootstrap(): array
    {
        $company = CompanyProfileModel::query()->find(CompanyProfile::SINGLETON_ID);
        $contacts = SiteContactsModel::query()->find(SiteContacts::SINGLETON_ID);
        $delivery = DeliveryInfoModel::query()->find(DeliveryInfo::SINGLETON_ID);

        if ($company === null || $contacts === null || $delivery === null) {
            throw new DomainException('Site content is not configured. Run SiteContentSeeder.');
        }

        $scheduleDays = ScheduleDayModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(static function (ScheduleDayModel $day): array {
                $payload = [
                    'id' => (int) $day->id,
                    'name' => (string) $day->name,
                    'is_day_off' => (bool) $day->is_day_off,
                ];

                if ($day->is_day_off) {
                    $payload['day_off_text'] = (string) $day->day_off_text;
                } else {
                    $payload['workshop'] = (string) $day->workshop;
                    $payload['delivery'] = (string) $day->delivery;
                }

                return $payload;
            })
            ->all();

        $prices = ServicePriceModel::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->map(static function (ServicePriceModel $price): array {
                $row = [
                    'category' => (string) $price->category,
                    'name' => (string) $price->name,
                    'price' => (string) $price->price,
                    'prefix' => $price->prefix,
                ];

                if ($price->description !== null && $price->description !== '') {
                    $row['description'] = (string) $price->description;
                }

                return $row;
            })
            ->all();

        $faqItems = FaqItemModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (FaqItemModel $item): array => [
                'id' => (int) $item->id,
                'question' => (string) $item->question,
                'answer_lines' => array_values((array) $item->answer_lines),
            ])
            ->all();

        $socialLinks = array_values((array) $contacts->social_links);

        return [
            'company' => [
                'owner_name' => (string) $company->owner_name,
                'inn' => (string) $company->inn,
                'ogrn' => (string) $company->ogrn,
                'legal_address' => (string) $company->legal_address,
                'actual_address' => (string) $company->actual_address,
            ],
            'contacts' => [
                'contact_person' => (string) $contacts->contact_person,
                'phone' => (string) $contacts->phone,
                'email' => (string) $contacts->email,
                'address' => [
                    'main' => (string) $contacts->address_main,
                    'directions' => (string) $contacts->entrance_directions,
                ],
                'social' => [
                    'email' => (string) $contacts->email,
                    'links' => $socialLinks,
                ],
            ],
            'schedule' => [
                'days' => $scheduleDays,
            ],
            'prices' => $prices,
            'delivery_info' => [
                'free_conditions' => array_values((array) $delivery->free_conditions),
                'advantages' => array_values((array) $delivery->advantages),
            ],
            'faq' => [
                'items' => $faqItems,
            ],
        ];
    }
}
