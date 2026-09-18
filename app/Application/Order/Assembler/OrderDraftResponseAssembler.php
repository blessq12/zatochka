<?php

namespace App\Application\Order\Assembler;

use App\Application\Order\DTO\OrderDraftResponse;
use App\Domain\Order\Aggregate\OrderDraft;

final readonly class OrderDraftResponseAssembler
{
    public function assemble(OrderDraft $draft): OrderDraftResponse
    {
        return new OrderDraftResponse(
            (int) $draft->id(),
            $draft->source()->value,
            $draft->status()->value,
            $draft->clientId(),
            $draft->fullName(),
            $draft->phone(),
            $draft->serviceType(),
            $draft->payload(),
            $draft->needsDelivery(),
            $draft->deliveryAddress(),
            $draft->comment(),
            $draft->orderId(),
            $draft->createdAt()?->format(DATE_ATOM),
            $draft->updatedAt()?->format(DATE_ATOM),
        );
    }
}
