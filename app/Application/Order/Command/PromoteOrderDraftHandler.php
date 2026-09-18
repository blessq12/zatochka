<?php

namespace App\Application\Order\Command;

use App\Application\Crm\Command\CreateWalkInClientHandler;
use App\Application\Order\Assembler\OrderDraftResponseAssembler;
use App\Application\Order\DTO\OrderDraftResponse;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Repository\OrderDraftRepository;
use App\Shared\Domain\DomainException;
use Illuminate\Support\Facades\DB;

final readonly class PromoteOrderDraftHandler
{
    public function __construct(
        private OrderDraftRepository $drafts,
        private CreateOrderHandler $createOrder,
        private CreateWalkInClientHandler $createWalkInClient,
        private OrderDraftResponseAssembler $draftAssembler,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{draft: OrderDraftResponse, order: OrderResponse}
     */
    public function handle(
        int $draftId,
        ?int $clientId,
        bool $createClient,
        string $billingType,
        string $urgency,
        string $estimatedCost,
        bool $needsDelivery,
        ?string $deliveryAddress,
        array $items,
    ): array {
        return DB::transaction(function () use (
            $draftId,
            $clientId,
            $createClient,
            $billingType,
            $urgency,
            $estimatedCost,
            $needsDelivery,
            $deliveryAddress,
            $items,
        ): array {
            $draft = $this->drafts->findById($draftId);
            if ($draft === null) {
                throw new DomainException('Order draft not found.');
            }
            if (! $draft->status()->isPending()) {
                throw new DomainException('Order draft can be changed only in pending status.');
            }

            $resolvedClientId = $this->resolveClientId($draft->clientId(), $clientId, $createClient, $draft->fullName(), $draft->phone());

            $order = $this->createOrder->handle(
                $resolvedClientId,
                $billingType,
                $urgency,
                $estimatedCost,
                $needsDelivery,
                $deliveryAddress,
                $items,
            );

            $draft->markPromoted($order->id);
            $savedDraft = $this->draftAssembler->assemble($this->drafts->save($draft));

            return [
                'draft' => $savedDraft,
                'order' => $order,
            ];
        });
    }

    private function resolveClientId(
        ?int $draftClientId,
        ?int $clientId,
        bool $createClient,
        ?string $fullName,
        ?string $phone,
    ): int {
        if ($draftClientId !== null && $draftClientId > 0) {
            if ($clientId !== null && $clientId !== $draftClientId) {
                throw new DomainException('client_id does not match draft client.');
            }

            return $draftClientId;
        }

        if ($clientId !== null && $clientId > 0) {
            return $clientId;
        }

        if ($createClient) {
            $created = $this->createWalkInClient->handle(
                (string) $fullName,
                (string) $phone,
            );

            return $created->id;
        }

        throw new DomainException('client_id is required or set create_client=true.');
    }
}
