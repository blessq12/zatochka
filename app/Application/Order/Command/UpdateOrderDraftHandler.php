<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Application\Order\Support\OrderDraftPayloadGuard;
use App\Domain\Order\OrderDraftSource;
use App\Domain\Order\Repository\OrderDraftRepository;

final readonly class UpdateOrderDraftHandler
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
        int $id,
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
        ?string $fullName = null,
        ?string $phone = null,
        ?int $asClientId = null,
    ): ?OrderDraftResponse {
        $draft = $this->drafts->findById($id);
        if ($draft === null) {
            return null;
        }
        if ($asClientId !== null) {
            if ($draft->source() !== OrderDraftSource::ClientLk || $draft->clientId() !== $asClientId) {
                return null;
            }
            $this->payloadGuard->assertClientPayload($asClientId, $serviceType, $payload);
        } elseif ($draft->source() === OrderDraftSource::ClientLk && $draft->clientId() !== null) {
            $this->payloadGuard->assertClientPayload((int) $draft->clientId(), $serviceType, $payload);
        }

        $draft->updatePending(
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
