<?php

namespace App\Application\SiteContent\Command;

final readonly class ReplaceFaqCatalogCommand
{
    /**
     * @param list<array{
     *     id?: ?int,
     *     question: string,
     *     answer_lines: list<string>
     * }> $items
     */
    public function __construct(
        public array $items,
    ) {}
}
