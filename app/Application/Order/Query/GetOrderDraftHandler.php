<?php

namespace App\Application\Order\Query;

use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Domain\Order\OrderDraftSource;
use App\Domain\Order\Repository\OrderDraftRepository;

final readonly class GetOrderDraftHandler
{
    public function __construct(
        private OrderDraftRepository $drafts,
        private OrderDraftResponseAssembler $assembler,
    ) {}

    public function handle(int $id, ?int $asClientId = null): ?OrderDraftResponse
    {
        $draft = $this->drafts->findById($id);
        if ($draft === null) {
            return null;
        }
        if ($asClientId !== null
            && ($draft->source() !== OrderDraftSource::ClientLk || $draft->clientId() !== $asClientId)
        ) {
            return null;
        }
        if ($asClientId !== null && $draft->status()->value === 'cancelled') {
            return null;
        }

        return $this->assembler->assemble($draft);
    }
}
