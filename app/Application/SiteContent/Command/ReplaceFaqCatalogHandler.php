<?php

namespace App\Application\SiteContent\Command;

use App\Application\Shared\EntityIdGenerator;
use App\Domain\SiteContent\Entity\FaqItem;
use App\Domain\SiteContent\Repository\FaqCatalogRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ReplaceFaqCatalogHandler
{
    public function __construct(
        private FaqCatalogRepository $catalogs,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(ReplaceFaqCatalogCommand $command): void
    {
        $items = [];

        foreach (array_values($command->items) as $index => $item) {
            $id = isset($item['id']) && $item['id'] !== null && $item['id'] !== ''
                ? new EntityId((int) $item['id'])
                : $this->ids->next('site_faq_item');

            $answerLines = $item['answer_lines'] ?? [];

            if (is_string($answerLines)) {
                $answerLines = preg_split("/\r\n|\n|\r/", $answerLines) ?: [];
            }

            $items[] = FaqItem::create(
                $id,
                (string) ($item['question'] ?? ''),
                array_values((array) $answerLines),
                $index + 1,
            );
        }

        $catalog = $this->catalogs->get();
        $catalog->replaceItems($items);
        $this->catalogs->save($catalog);
    }
}
