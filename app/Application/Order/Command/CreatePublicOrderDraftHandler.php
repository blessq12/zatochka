<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Domain\Order\Aggregate\OrderDraft;
use App\Domain\Order\Repository\OrderDraftRepository;

final readonly class CreatePublicOrderDraftHandler
{
    public function __construct(
        private OrderDraftRepository $drafts,
        private OrderDraftResponseAssembler $assembler,
    ) {}

    /**
     * @param  array<string, mixed>  $intake
     */
    public function handle(
        string $fullName,
        string $phone,
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
    ): OrderDraftResponse {
        $draft = OrderDraft::createPublic(
            $fullName,
            $phone,
            $serviceType,
            $payload,
            $needsDelivery,
            $deliveryAddress,
            $comment,
        );

        return $this->assembler->assemble($this->drafts->save($draft));
    }
}
