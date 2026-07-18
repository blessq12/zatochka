<?php

namespace App\Application\SiteContent\Command;

final readonly class UpdateDeliveryInfoCommand
{
    /**
     * @param  list<string>  $freeConditions
     * @param  list<array{title: string, description: string}>  $advantages
     */
    public function __construct(
        public array $freeConditions,
        public array $advantages,
    ) {}
}
