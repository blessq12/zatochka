<?php

namespace App\Application\SiteContent\Command;

final readonly class ReplaceServicePriceListCommand
{
    /**
     * @param list<array{
     *     id?: ?int,
     *     type: string,
     *     title: string,
     *     items: list<array{
     *         id?: ?int,
     *         name: string,
     *         price: string,
     *         prefix?: ?string,
     *         description?: ?string
     *     }>
     * }> $blocks
     */
    public function __construct(
        public array $blocks,
    ) {}
}
