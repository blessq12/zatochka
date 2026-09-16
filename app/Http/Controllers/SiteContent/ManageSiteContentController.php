<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Command\ReplaceFaqCatalogCommand;
use App\Application\SiteContent\Command\ReplaceFaqCatalogHandler;
use App\Application\SiteContent\Command\ReplaceWorkScheduleCommand;
use App\Application\SiteContent\Command\ReplaceWorkScheduleHandler;
use App\Application\SiteContent\Command\UpdateCompanyProfileCommand;
use App\Application\SiteContent\Command\UpdateCompanyProfileHandler;
use App\Application\SiteContent\Command\UpdateDeliveryInfoCommand;
use App\Application\SiteContent\Command\UpdateDeliveryInfoHandler;
use App\Application\SiteContent\Command\UpdateSiteContactsCommand;
use App\Application\SiteContent\Command\UpdateSiteContactsHandler;
use App\Http\Controllers\Controller;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ManageSiteContentController extends Controller
{
    public function __construct(
        private UpdateCompanyProfileHandler $updateCompany,
        private UpdateSiteContactsHandler $updateContacts,
        private UpdateDeliveryInfoHandler $updateDelivery,
        private ReplaceWorkScheduleHandler $replaceSchedule,
        private ReplaceFaqCatalogHandler $replaceFaq,
    ) {}

    public function show(): JsonResponse
    {
        return $this->ok($this->loadFormData());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company' => ['required', 'array'],
            'company.owner_name' => ['required', 'string'],
            'company.inn' => ['required', 'string'],
            'company.ogrn' => ['required', 'string'],
            'company.legal_address' => ['required', 'string'],
            'company.actual_address' => ['required', 'string'],
            'contacts' => ['required', 'array'],
            'contacts.contact_person' => ['required', 'string'],
            'contacts.phone' => ['required', 'string'],
            'contacts.email' => ['required', 'email'],
            'contacts.address_main' => ['required', 'string'],
            'contacts.entrance_directions' => ['nullable', 'string'],
            'contacts.social_links' => ['nullable', 'array'],
            'delivery' => ['required', 'array'],
            'delivery.free_conditions' => ['nullable', 'array'],
            'delivery.advantages' => ['nullable', 'array'],
            'schedule' => ['required', 'array'],
            'schedule.days' => ['required', 'array'],
            'faq' => ['required', 'array'],
            'faq.items' => ['nullable', 'array'],
        ]);

        $company = $data['company'];
        $contacts = $data['contacts'];
        $delivery = $data['delivery'];
        $schedule = $data['schedule'];
        $faq = $data['faq'];

        $this->updateCompany->handle(new UpdateCompanyProfileCommand(
            (string) $company['owner_name'],
            (string) $company['inn'],
            (string) $company['ogrn'],
            (string) $company['legal_address'],
            (string) $company['actual_address'],
        ));

        $this->updateContacts->handle(new UpdateSiteContactsCommand(
            (string) $contacts['contact_person'],
            (string) $contacts['phone'],
            (string) $contacts['email'],
            (string) $contacts['address_main'],
            (string) ($contacts['entrance_directions'] ?? ''),
            array_values((array) ($contacts['social_links'] ?? [])),
        ));

        $this->updateDelivery->handle(new UpdateDeliveryInfoCommand(
            array_values((array) ($delivery['free_conditions'] ?? [])),
            array_values((array) ($delivery['advantages'] ?? [])),
        ));

        $this->replaceSchedule->handle(new ReplaceWorkScheduleCommand(
            array_values((array) ($schedule['days'] ?? [])),
        ));

        $faqItems = [];
        foreach ((array) ($faq['items'] ?? []) as $item) {
            $answer = $item['answer_lines'] ?? [];
            if (is_string($answer)) {
                $answer = array_values(array_filter(array_map('trim', explode("\n", $answer))));
            }
            $faqItems[] = [
                'id' => isset($item['id']) ? (int) $item['id'] : null,
                'question' => (string) ($item['question'] ?? ''),
                'answer_lines' => array_values((array) $answer),
            ];
        }

        $this->replaceFaq->handle(new ReplaceFaqCatalogCommand($faqItems));

        return $this->ok($this->loadFormData());
    }

    /** @return array<string, mixed> */
    private function loadFormData(): array
    {
        $company = CompanyProfileModel::query()->find(1);
        $contacts = SiteContactsModel::query()->find(1);
        $delivery = DeliveryInfoModel::query()->find(1);

        return [
            'company' => [
                'owner_name' => (string) ($company?->owner_name ?? ''),
                'inn' => (string) ($company?->inn ?? ''),
                'ogrn' => (string) ($company?->ogrn ?? ''),
                'legal_address' => (string) ($company?->legal_address ?? ''),
                'actual_address' => (string) ($company?->actual_address ?? ''),
            ],
            'contacts' => [
                'contact_person' => (string) ($contacts?->contact_person ?? ''),
                'phone' => (string) ($contacts?->phone ?? ''),
                'email' => (string) ($contacts?->email ?? ''),
                'address_main' => (string) ($contacts?->address_main ?? ''),
                'entrance_directions' => (string) ($contacts?->entrance_directions ?? ''),
                'social_links' => array_values((array) ($contacts?->social_links ?? [])),
            ],
            'delivery' => [
                'free_conditions' => array_values((array) ($delivery?->free_conditions ?? [])),
                'advantages' => array_values((array) ($delivery?->advantages ?? [])),
            ],
            'schedule' => [
                'days' => ScheduleDayModel::query()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(static fn (ScheduleDayModel $day): array => [
                        'id' => (int) $day->id,
                        'name' => (string) $day->name,
                        'is_day_off' => (bool) $day->is_day_off,
                        'day_off_text' => $day->day_off_text,
                        'workshop' => $day->workshop,
                        'delivery' => $day->delivery,
                    ])
                    ->all(),
            ],
            'faq' => [
                'items' => FaqItemModel::query()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(static fn (FaqItemModel $item): array => [
                        'id' => (int) $item->id,
                        'question' => (string) $item->question,
                        'answer_lines' => implode("\n", array_values((array) $item->answer_lines)),
                    ])
                    ->all(),
            ],
        ];
    }
}
