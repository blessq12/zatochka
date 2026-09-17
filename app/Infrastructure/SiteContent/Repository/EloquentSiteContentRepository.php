<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Repository\SiteContentRepository;
use App\Infrastructure\SiteContent\Eloquent\SiteCompanyModel;
use App\Infrastructure\SiteContent\Eloquent\SiteContactModel;
use App\Infrastructure\SiteContent\Eloquent\SiteDeliveryAdvantageModel;
use App\Infrastructure\SiteContent\Eloquent\SiteDeliveryConditionModel;
use App\Infrastructure\SiteContent\Eloquent\SiteFaqItemModel;
use App\Infrastructure\SiteContent\Eloquent\SiteLegalDocumentModel;
use App\Infrastructure\SiteContent\Eloquent\SitePriceItemModel;
use App\Infrastructure\SiteContent\Eloquent\SiteScheduleDayModel;
use App\Infrastructure\SiteContent\Eloquent\SiteSocialLinkModel;
use Illuminate\Support\Facades\DB;

final class EloquentSiteContentRepository implements SiteContentRepository
{
    public function bootstrap(): array
    {
        $company = SiteCompanyModel::query()->first();
        $contact = SiteContactModel::query()->first();
        $links = SiteSocialLinkModel::query()->orderBy('sort_order')->orderBy('id')->get();
        $days = SiteScheduleDayModel::query()->orderBy('sort_order')->orderBy('id')->get();
        $faq = SiteFaqItemModel::query()->orderBy('sort_order')->orderBy('id')->get();
        $conditions = SiteDeliveryConditionModel::query()->orderBy('sort_order')->orderBy('id')->get();
        $advantages = SiteDeliveryAdvantageModel::query()->orderBy('sort_order')->orderBy('id')->get();
        $prices = SitePriceItemModel::query()->orderBy('sort_order')->orderBy('id')->get();

        return [
            'company' => [
                'name' => (string) ($company?->name ?? ''),
                'tagline' => $company?->tagline,
                'owner_name' => $company?->owner_name,
                'inn' => $company?->inn,
                'ogrn' => $company?->ogrn,
                'legal_address' => $company?->legal_address,
                'actual_address' => $company?->actual_address,
            ],
            'contacts' => [
                'phone' => $contact?->phone,
                'email' => $contact?->email,
                'contact_person' => $contact?->contact_person,
                'address' => [
                    'main' => $contact?->address_main,
                    'directions' => $contact?->address_directions,
                ],
                'social' => [
                    'email' => $contact?->social_email ?: $contact?->email,
                    'links' => $links->map(static fn (SiteSocialLinkModel $link): array => [
                        'name' => (string) $link->name,
                        'url' => (string) $link->url,
                    ])->values()->all(),
                ],
            ],
            'schedule' => [
                'days' => $days->map(static fn (SiteScheduleDayModel $day): array => [
                    'label' => (string) $day->label,
                    'hours' => (string) $day->hours,
                ])->values()->all(),
            ],
            'faq' => [
                'items' => $faq->map(static fn (SiteFaqItemModel $item): array => [
                    'question' => (string) $item->question,
                    'answer_lines' => array_values((array) ($item->answer_lines ?? [])),
                ])->values()->all(),
            ],
            'delivery_info' => [
                'free_conditions' => $conditions->map(
                    static fn (SiteDeliveryConditionModel $row): string => (string) $row->text,
                )->values()->all(),
                'advantages' => $advantages->map(static fn (SiteDeliveryAdvantageModel $row): array => [
                    'title' => (string) $row->title,
                    'text' => (string) $row->text,
                ])->values()->all(),
            ],
            'prices' => $prices->map(static fn (SitePriceItemModel $row): array => [
                'category' => (string) $row->category,
                'name' => (string) $row->name,
                'description' => $row->description,
                'price' => (string) $row->price,
                'prefix' => $row->prefix,
            ])->values()->all(),
        ];
    }

    public function legalDocumentBySlug(string $slug): ?array
    {
        $document = SiteLegalDocumentModel::query()->where('slug', $slug)->first();

        return $document === null ? null : $this->mapLegal($document);
    }

    public function legalDocuments(): array
    {
        return SiteLegalDocumentModel::query()
            ->orderBy('slug')
            ->get()
            ->map(fn (SiteLegalDocumentModel $document): array => $this->mapLegal($document))
            ->values()
            ->all();
    }

    public function saveCompany(array $data): void
    {
        $company = SiteCompanyModel::query()->first() ?? new SiteCompanyModel();
        $company->fill([
            'name' => (string) ($data['name'] ?? ''),
            'tagline' => $data['tagline'] ?? null,
            'owner_name' => $data['owner_name'] ?? null,
            'inn' => $data['inn'] ?? null,
            'ogrn' => $data['ogrn'] ?? null,
            'legal_address' => $data['legal_address'] ?? null,
            'actual_address' => $data['actual_address'] ?? null,
        ]);
        $company->save();
    }

    public function saveContacts(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $address = (array) ($data['address'] ?? []);
            $social = (array) ($data['social'] ?? []);

            $contact = SiteContactModel::query()->first() ?? new SiteContactModel();
            $contact->fill([
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'address_main' => $address['main'] ?? null,
                'address_directions' => $address['directions'] ?? null,
                'social_email' => $social['email'] ?? ($data['email'] ?? null),
            ]);
            $contact->save();

            SiteSocialLinkModel::query()->delete();
            foreach (array_values((array) ($social['links'] ?? [])) as $index => $link) {
                SiteSocialLinkModel::query()->create([
                    'name' => (string) ($link['name'] ?? ''),
                    'url' => (string) ($link['url'] ?? ''),
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function saveSchedule(array $days): void
    {
        DB::transaction(function () use ($days): void {
            SiteScheduleDayModel::query()->delete();
            foreach (array_values($days) as $index => $day) {
                SiteScheduleDayModel::query()->create([
                    'label' => (string) ($day['label'] ?? ''),
                    'hours' => (string) ($day['hours'] ?? ''),
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function saveFaq(array $items): void
    {
        DB::transaction(function () use ($items): void {
            SiteFaqItemModel::query()->delete();
            foreach (array_values($items) as $index => $item) {
                $lines = $item['answer_lines'] ?? [];
                if (is_string($lines)) {
                    $lines = preg_split("/\r\n|\n|\r/", $lines) ?: [];
                }

                SiteFaqItemModel::query()->create([
                    'question' => (string) ($item['question'] ?? ''),
                    'answer_lines' => array_values(array_filter(
                        array_map(static fn ($line) => trim((string) $line), (array) $lines),
                        static fn (string $line): bool => $line !== '',
                    )),
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function saveDelivery(array $data): void
    {
        DB::transaction(function () use ($data): void {
            SiteDeliveryConditionModel::query()->delete();
            foreach (array_values((array) ($data['free_conditions'] ?? [])) as $index => $text) {
                SiteDeliveryConditionModel::query()->create([
                    'text' => (string) $text,
                    'sort_order' => $index,
                ]);
            }

            SiteDeliveryAdvantageModel::query()->delete();
            foreach (array_values((array) ($data['advantages'] ?? [])) as $index => $row) {
                SiteDeliveryAdvantageModel::query()->create([
                    'title' => (string) ($row['title'] ?? ''),
                    'text' => (string) ($row['text'] ?? ''),
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function savePrices(array $items): void
    {
        DB::transaction(function () use ($items): void {
            SitePriceItemModel::query()->delete();
            foreach (array_values($items) as $index => $item) {
                SitePriceItemModel::query()->create([
                    'category' => (string) ($item['category'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'description' => $item['description'] ?? null,
                    'price' => (string) ($item['price'] ?? ''),
                    'prefix' => $item['prefix'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function saveLegalDocument(array $data): array
    {
        $slug = (string) ($data['slug'] ?? '');
        $document = SiteLegalDocumentModel::query()->where('slug', $slug)->first()
            ?? new SiteLegalDocumentModel(['slug' => $slug]);

        $document->fill([
            'slug' => $slug,
            'type' => (string) ($data['type'] ?? $slug),
            'title' => (string) ($data['title'] ?? ''),
            'body_html' => (string) ($data['body_html'] ?? ''),
        ]);
        $document->save();

        return $this->mapLegal($document->fresh());
    }

    public function deleteLegalDocument(string $slug): bool
    {
        $document = SiteLegalDocumentModel::query()->where('slug', $slug)->first();
        if ($document === null) {
            return false;
        }

        $document->delete();

        return true;
    }

    /**
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}
     */
    private function mapLegal(SiteLegalDocumentModel $document): array
    {
        return [
            'type' => (string) $document->type,
            'slug' => (string) $document->slug,
            'title' => (string) $document->title,
            'body_html' => (string) $document->body_html,
            'updated_at' => $document->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
