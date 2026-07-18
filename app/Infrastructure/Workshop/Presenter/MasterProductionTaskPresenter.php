<?php

namespace App\Infrastructure\Workshop\Presenter;

use App\Application\Workshop\DTO\MasterProductionTaskCardDTO;
use App\Domain\Order\Service\OrderItemRejectionPolicy;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final class MasterProductionTaskPresenter
{
    public function present(ProductionTaskModel $model): MasterProductionTaskCardDTO
    {
        $order = $model->order;
        $client = $order?->client;
        $orderItems = $order?->items ?? collect();

        $works = [];
        $masterInternalComments = [];

        foreach ($model->performedWorks as $index => $work) {
            $works[] = [
                'id' => (int) $work->id,
                'description' => (string) $work->description,
                'sort_order' => $index + 1,
                'created_at' => $work->created_at?->toIso8601String() ?? '',
                'order_item_id' => (int) $work->order_item_id,
                'equipment_component_id' => $work->equipment_component_id !== null
                    ? (int) $work->equipment_component_id
                    : null,
            ];
        }

        foreach ($model->master_comments ?? [] as $comment) {
            if (! is_array($comment)) {
                continue;
            }

            $masterInternalComments[] = [
                'id' => (int) $comment['id'],
                'text' => (string) $comment['text'],
                'created_at' => (string) ($comment['created_at'] ?? ''),
            ];
        }

        $internalNotes = $masterInternalComments === []
            ? null
            : implode("\n\n", array_column($masterInternalComments, 'text'));

        $items = [];
        $toolsSummary = [];
        $equipmentList = [];
        $subjectParts = [];

        foreach ($orderItems as $item) {
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;
            $rejectedQuantity = (int) ($item->rejected_quantity ?? 0);
            $status = (string) $item->status;
            $repairableQuantity = OrderItemRejectionPolicy::repairableQuantity($quantity, $rejectedQuantity, $status);

            $components = [];
            $serialNumbers = [];

            $equipmentModel = $item->equipment;
            if ($equipmentModel !== null) {
                foreach ($equipmentModel->components ?? [] as $component) {
                    $componentName = (string) $component->name;
                    $serial = $component->serial_number !== null && trim((string) $component->serial_number) !== ''
                        ? (string) $component->serial_number
                        : null;

                    $components[] = [
                        'id' => (int) $component->id,
                        'name' => $componentName,
                        'serial_number' => $serial,
                    ];

                    if ($serial !== null) {
                        $serialNumbers[$componentName] = $serial;
                    }
                }

                $equipmentList[] = [
                    'id' => (int) $equipmentModel->id,
                    'name' => (string) $equipmentModel->title,
                    'brand' => (string) $equipmentModel->brand,
                    'model' => (string) $equipmentModel->model_name,
                    'serial_numbers' => $serialNumbers,
                    'components' => $components,
                ];

                $label = trim(($equipmentModel->brand ?? '').' '.($equipmentModel->model_name ?? ''));
                if ($label !== '') {
                    $subjectParts[] = $label;
                }
            }

            $items[] = [
                'id' => (int) $item->id,
                'tool_name' => $item->tool_name !== null ? (string) $item->tool_name : null,
                'tool_type' => $item->tool_type !== null ? (string) $item->tool_type : null,
                'quantity' => $quantity,
                'rejected_quantity' => $rejectedQuantity,
                'repairable_quantity' => $repairableQuantity,
                'status' => $status,
                'client_equipment_id' => $item->client_equipment_id !== null
                    ? (int) $item->client_equipment_id
                    : null,
                'components' => $components,
            ];

            if ($item->tool_name !== null) {
                $toolsSummary[] = [
                    'tool_type' => $item->tool_type !== null ? (string) $item->tool_type : null,
                    'name' => (string) $item->tool_name,
                    'quantity' => $quantity ?? 1,
                    'rejected_quantity' => $rejectedQuantity,
                    'repairable_quantity' => $repairableQuantity,
                ];
                $subjectParts[] = (string) $item->tool_name.($quantity !== null ? ' ×'.$quantity : '');
            }
        }

        $subjectLine = $subjectParts === [] ? null : implode(', ', $subjectParts);
        $status = (string) $model->status;
        $serviceType = (string) ($order?->service_type ?? '');
        $workTargetMode = $serviceType === OrderServiceType::Repair->value
            ? 'equipment_component'
            : 'order_item';

        return new MasterProductionTaskCardDTO(
            (int) $model->id,
            $status,
            $this->toPosStatus($status),
            $model->master_id !== null ? (int) $model->master_id : null,
            (string) ($order?->id ?? ''),
            (string) ($order?->number ?? ''),
            $serviceType,
            (string) ($order?->billing_type ?? ''),
            (string) ($order?->urgency ?? ''),
            (bool) ($order?->delivery_required ?? false),
            $order?->defects !== null ? (string) $order->defects : null,
            $internalNotes,
            $order?->manager_rework_comment !== null ? (string) $order->manager_rework_comment : null,
            $order?->created_at?->toIso8601String() ?? ($model->created_at?->toIso8601String() ?? ''),
            $client?->name !== null ? (string) $client->name : null,
            $client?->phone !== null ? (string) $client->phone : null,
            $items,
            $works,
            $masterInternalComments,
            $toolsSummary,
            $equipmentList,
            $subjectLine,
            $order?->defects !== null ? mb_substr((string) $order->defects, 0, 160) : null,
            $workTargetMode,
        );
    }

    private function toPosStatus(string $productionStatus): string
    {
        return match ($productionStatus) {
            ProductionStatus::MasterAssigned->value => 'new',
            ProductionStatus::Diagnosed->value, ProductionStatus::InWork->value => 'in_work',
            ProductionStatus::WaitingParts->value => 'waiting_parts',
            ProductionStatus::WorkCompleted->value, ProductionStatus::Completed->value => 'ready',
            ProductionStatus::Rejected->value => 'cancelled',
            default => $productionStatus,
        };
    }

    public function serviceTypeLabel(string $serviceType): string
    {
        return OrderServiceType::tryLabel($serviceType) ?? $serviceType;
    }

    public function billingLabel(string $billingType): bool
    {
        return $billingType === OrderBillingType::Warranty->value;
    }
}
