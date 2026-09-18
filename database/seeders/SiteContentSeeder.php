<?php

namespace Database\Seeders;

use App\Domain\SiteContent\Repository\SiteContentRepository;
use App\Infrastructure\SiteContent\Eloquent\SiteCompanyModel;
use App\Infrastructure\SiteContent\Fixture\SiteContentFixture;
use Illuminate\Database\Seeder;

final class SiteContentSeeder extends Seeder
{
    public function run(SiteContentRepository $siteContent): void
    {
        if (SiteCompanyModel::query()->exists()) {
            $this->command?->info('Site content already seeded.');

            return;
        }

        $mock = SiteContentFixture::bootstrap();

        $siteContent->saveCompany((array) $mock['company']);
        $siteContent->saveContacts((array) $mock['contacts']);
        $siteContent->saveSchedule((array) ($mock['schedule']['days'] ?? []));
        $siteContent->saveFaq((array) ($mock['faq']['items'] ?? []));
        $siteContent->saveDelivery((array) $mock['delivery_info']);
        $siteContent->savePrices((array) $mock['prices']);

        foreach (['privacy-policy', 'user-agreement', 'usage-rules'] as $slug) {
            $document = SiteContentFixture::legalDocumentBySlug($slug);
            if ($document !== null) {
                $siteContent->saveLegalDocument($document);
            }
        }

        $this->command?->info('Site content seeded from fixture.');
    }
}
