<?php

namespace App\Application\OrderFulfillment\Query;

use App\Domain\OrderFulfillment\Enum\PosOrderListTab;

final readonly class GetPosOrdersQuery
{
    public function __construct(
        public int $masterId,
        public ?PosOrderListTab $tab,
        public int $page = 1,
        public int $perPage = 20,
    ) {}
}
