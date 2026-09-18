<?php

namespace Database\Seeders;

use App\Domain\Order\Enum\DocumentType;
use App\Infrastructure\Order\Document\DefaultDocumentTemplateBodies;
use App\Infrastructure\Order\Eloquent\DocumentTemplateModel;
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
