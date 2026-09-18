<?php

namespace App\Application\Order\Query;

use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Domain\Order\Repository\OrderDraftRepository;

final readonly class ListOrderDraftsHandler
{
    public function __construct(
        private OrderDraftRepository $drafts,
        private OrderDraftResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<string>|null  $statusesIn
     * @return list<OrderDraftResponse>
     */
    public function handle(
        ?int $clientId = null,
        ?string $status = null,
        ?string $source = null,
        ?string $phone = null,
        ?array $statusesIn = null,
    ): array {
        $items = $this->drafts->all($clientId, $status, $source, $phone, $statusesIn);

        return array_map(
            fn ($draft): OrderDraftResponse => $this->assembler->assemble($draft),
            $items,
        );
    }
}
