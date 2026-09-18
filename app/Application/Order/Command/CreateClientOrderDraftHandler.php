<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Application\Order\Support\OrderDraftPayloadGuard;
use App\Domain\Order\Aggregate\OrderDraft;
use App\Domain\Order\Repository\OrderDraftRepository;

final readonly class CreateClientOrderDraftHandler
{
    public function __construct(
        private OrderDraftRepository $drafts,
        private OrderDraftPayloadGuard $payloadGuard,
        private OrderDraftResponseAssembler $assembler,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(
        int $clientId,
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
        ?string $fullName = null,
        ?string $phone = null,
    ): OrderDraftResponse {
        $this->payloadGuard->assertClientPayload($clientId, $serviceType, $payload);

        $draft = OrderDraft::createForClient(
            $clientId,
            $serviceType,
            $payload,
            $needsDelivery,
            $deliveryAddress,
            $comment,
            $fullName,
            $phone,
        );

        return $this->assembler->assemble($this->drafts->save($draft));
    }
}
