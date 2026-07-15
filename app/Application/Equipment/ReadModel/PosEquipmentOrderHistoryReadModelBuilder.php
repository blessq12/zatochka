<?php

namespace App\Application\Equipment\ReadModel;

use App\Application\OrderFulfillment\Presenter\PosOrderPresenter;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;

final class PosEquipmentOrderHistoryReadModelBuilder
{
    public function __construct(
        private MasterRepositoryInterface $masters,
    ) {}

    /** @param list<Order> $orders */
    public function list(array $orders): array
    {
        $masterNames = $this->resolveMasterNames($orders);

        return array_map(
            fn (Order $order): array => $this->build($order, $masterNames),
            $orders,
        );
    }

    /**
     * @param  array<int, string|null>  $masterNames
     * @return array<string, mixed>
     */
    public function build(Order $order, array $masterNames = []): array
    {
        $masterId = $order->masterId();

        return [
            ...PosOrderPresenter::listItem($order),
            'problem_description' => $order->problemDescription(),
            'internal_notes' => $order->internalNotes(),
            'master_name' => $masterId !== null
                ? ($masterNames[$masterId] ?? $this->masterName($masterId))
                : null,
            'works' => array_map(
                static fn (OrderWork $work): array => [
                    'id' => $work->id,
                    'description' => $work->description,
                    'price' => $work->price,
                ],
                $order->works(),
            ),
            'works_count' => count($order->works()),
        ];
    }

    /** @param list<Order> $orders */
    /** @return array<int, string|null> */
    private function resolveMasterNames(array $orders): array
    {
        $masterNames = [];

        foreach ($orders as $order) {
            $masterId = $order->masterId();

            if ($masterId === null || array_key_exists($masterId, $masterNames)) {
                continue;
            }

            $masterNames[$masterId] = $this->masterName($masterId);
        }

        return $masterNames;
    }

    private function masterName(int $masterId): ?string
    {
        return $this->masters->findById($masterId)?->fullName();
    }
}
