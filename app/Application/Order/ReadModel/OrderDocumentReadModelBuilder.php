<?php

namespace App\Application\Order\ReadModel;

use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\EquipmentRepository;
use App\Domain\Crm\Repository\MasterRepository;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\OrderItemKind;
use App\Domain\Order\Urgency;
use App\Domain\SiteContent\Repository\SiteContentRepository;
use App\Domain\Warehouse\Repository\OrderIssueRepository;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Infrastructure\Order\Eloquent\OrderModel;

final class OrderDocumentReadModelBuilder
{
    public function __construct(
        private SiteContentRepository $siteContent,
        private ClientRepository $clients,
        private MasterRepository $masters,
        private ProfileAdditionalRepository $profiles,
        private EquipmentRepository $equipment,
        private WorkshopJobRepository $workshopJobs,
        private OrderPricingRepository $pricings,
        private OrderIssueRepository $orderIssues,
        private StockItemRepository $stockItems,
    ) {}

    public function build(Order $order, ?string $managerName = null): OrderDocumentData
    {
        $bootstrap = $this->siteContent->bootstrap();
        $company = $bootstrap['company'] ?? [];
        $contacts = $bootstrap['contacts'] ?? [];

        $clientName = '—';
        $clientPhone = '—';
        $client = $this->clients->findById($order->clientId());
        if ($client !== null) {
            $profile = $this->profiles->findById($client->profileAdditionalId());
            $clientName = $profile?->name() ?: '—';
            $clientPhone = $profile?->phone() ?: '—';
        }

        $masterName = null;
        if ($order->masterId() !== null) {
            $master = $this->masters->findById((int) $order->masterId());
            if ($master !== null) {
                $profile = $this->profiles->findById($master->profileAdditionalId());
                $masterName = $profile?->name();
            }
        }

        $tools = [];
        $equipmentNames = [];
        $problems = [];
        $kinds = [];

        foreach ($order->items() as $item) {
            $kinds[] = $item->kind()->value;
            if ($item->kind() === OrderItemKind::Sharpening) {
                $tools[] = [
                    'type' => $item->title() ?: 'Инструмент',
                    'quantity' => $item->quantity() ?? 1,
                ];
            }
            if ($item->kind() === OrderItemKind::Repair) {
                if ($item->equipmentId() !== null) {
                    $eq = $this->equipment->findById((int) $item->equipmentId());
                    if ($eq !== null) {
                        $equipmentNames[] = trim(implode(' ', array_filter([
                            $eq->name(),
                            $eq->brand(),
                            $eq->type(),
                        ])));
                    }
                }
                if ($item->problem()) {
                    $problems[] = $item->problem();
                }
            }
        }

        $works = $this->buildWorks($order);
        $materials = $this->buildMaterials($order);

        $price = (float) $order->estimatedCost();
        $pricing = $this->pricings->findByOrderId((int) $order->id());
        if ($pricing !== null) {
            $price = (float) $pricing->total();
        }

        $orderModel = OrderModel::query()->find($order->id());
        $orderDate = $orderModel?->created_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i');

        $address = $contacts['address']['main'] ?? null;
        if (! is_string($address) || $address === '') {
            $address = $company['actual_address'] ?? $company['legal_address'] ?? null;
        }

        return new OrderDocumentData(
            orderNumber: (string) $order->id(),
            orderDate: $orderDate,
            serviceTypeLabel: $this->serviceTypeLabel($kinds),
            urgency: $order->urgency() === Urgency::Urgent ? 'Срочный' : null,
            branchName: (string) ($company['name'] ?? '—'),
            branchAddress: is_string($address) ? $address : null,
            branchPhone: is_string($contacts['phone'] ?? null) ? $contacts['phone'] : null,
            clientName: $clientName,
            clientPhone: $clientPhone,
            equipmentName: $equipmentNames !== [] ? implode(', ', $equipmentNames) : null,
            tools: $tools,
            problemDescription: $problems !== [] ? implode('; ', $problems) : null,
            price: $price,
            managerName: $managerName,
            masterName: $masterName,
            companyName: $company['name'] ?? null,
            companyLegalName: $company['owner_name'] ?? null,
            companyInn: $company['inn'] ?? null,
            companyKpp: null,
            companyOgrn: $company['ogrn'] ?? null,
            companyAddress: is_string($address) ? $address : null,
            companyPhone: is_string($contacts['phone'] ?? null) ? $contacts['phone'] : null,
            works: $works,
            materials: $materials,
        );
    }

    /**
     * @param  list<string>  $kinds
     */
    private function serviceTypeLabel(array $kinds): string
    {
        $labels = [];
        foreach (array_unique($kinds) as $kind) {
            $labels[] = match ($kind) {
                'sharpening' => 'Заточка',
                'repair' => 'Ремонт',
                default => $kind,
            };
        }

        return $labels !== [] ? implode(', ', $labels) : '—';
    }

    /**
     * @return list<array{description: string, price: float}>
     */
    private function buildWorks(Order $order): array
    {
        $job = $this->workshopJobs->findByOrderId((int) $order->id());
        if ($job === null) {
            return [];
        }

        $pricing = $this->pricings->findByOrderId((int) $order->id());
        $amounts = [];
        if ($pricing !== null) {
            foreach ($pricing->lines() as $line) {
                $amounts[$line->workEntryId()] = (float) $line->amount();
            }
        }

        $works = [];
        foreach ($job->items() as $itemWork) {
            foreach ($itemWork->works() as $work) {
                $id = (int) ($work->id() ?? 0);
                $works[] = [
                    'description' => $work->title(),
                    'price' => $amounts[$id] ?? 0.0,
                ];
            }
        }

        return $works;
    }

    /**
     * @return list<array{name: string, quantity: string, price: float}>
     */
    private function buildMaterials(Order $order): array
    {
        $pricing = $this->pricings->findByOrderId((int) $order->id());
        if ($pricing === null || $pricing->materialLines() === []) {
            return [];
        }

        $qtyByStock = [];
        $issue = $this->orderIssues->findByOrderId((int) $order->id());
        if ($issue !== null) {
            foreach ($issue->lines() as $line) {
                $qtyByStock[$line->stockItemId()] = $line->qty();
            }
        }

        $materials = [];
        foreach ($pricing->materialLines() as $line) {
            $item = $this->stockItems->findById($line->stockItemId());
            $materials[] = [
                'name' => $item?->name() ?? 'Позиция #'.$line->stockItemId(),
                'quantity' => $qtyByStock[$line->stockItemId()] ?? '1',
                'price' => (float) $line->amount(),
            ];
        }

        return $materials;
    }
}
