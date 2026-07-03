<?php

namespace Database\Seeders;

use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Infrastructure\OrderFulfillment\Document\DefaultDocumentTemplateBodies;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\DocumentTemplateModel;
use Illuminate\Database\Seeder;

final class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DocumentType::cases() as $type) {
            DocumentTemplateModel::query()->updateOrCreate(
                ['type' => $type->value],
                ['body' => DefaultDocumentTemplateBodies::forType($type)],
            );
        }
    }
}
