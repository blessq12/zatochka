<?php

namespace App\Infrastructure\Workshop\Presenter;

use App\Application\Workshop\DTO\MasterProductionTaskCardDTO;
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

        foreach ($model->comments as $index => $comment) {
            if ($comment->order_item_id !== null) {
                $works[] = [
                    'id' => (int) $comment->id,
                    'description' => (string) $comment->text,
                    'sort_order' => count($works) + 1,
                    'created_at' => $comment->created_at?->toIso8601String() ?? '',
                    'order_item_id' => (int) $comment->order_item_id,
                ];

                continue;
            }

            $masterInternalComments[] = [
                'id' => (int) $comment->id,
                'text' => (string) $comment->text,
                'created_at' => $comment->created_at?->toIso8601String() ?? '',
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
            $repairableQuantity = $quantity !== null
                ? max(0, $quantity - $rejectedQuantity)
                : ($status === 'rejected' ? 0 : 1);

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

            $equipmentModel = $item->equipment;
            if ($equipmentModel !== null) {
                $serialNumbers = [];
                foreach ($equipmentModel->components ?? [] as $component) {
                    if ($component->serial_number !== null && trim((string) $component->serial_number) !== '') {
                        $serialNumbers[(string) $component->name] = (string) $component->serial_number;
                    }
                }

                $equipmentList[] = [
                    'id' => (int) $equipmentModel->id,
                    'name' => (string) $equipmentModel->title,
                    'brand' => (string) $equipmentModel->brand,
                    'model' => (string) $equipmentModel->model_name,
                    'serial_numbers' => $serialNumbers,
                ];

                $label = trim(($equipmentModel->brand ?? '').' '.($equipmentModel->model_name ?? ''));
                if ($label !== '') {
                    $subjectParts[] = $label;
                }
            }
        }

        $subjectLine = $subjectParts === [] ? null : implode(', ', $subjectParts);
        $status = (string) $model->status;

        return new MasterProductionTaskCardDTO(
            (int) $model->id,
            $status,
            $this->toPosStatus($status),
            $model->master_id !== null ? (int) $model->master_id : null,
            (string) ($order?->id ?? ''),
            (string) ($order?->number ?? ''),
            (string) ($order?->service_type ?? ''),
            (string) ($order?->billing_type ?? ''),
            (string) ($order?->urgency ?? ''),
            (bool) ($order?->delivery_required ?? false),
            $order?->defects !== null ? (string) $order->defects : null,
            $internalNotes,
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
        );
    }

    private function toPosStatus(string $productionStatus): string
    {
        return match ($productionStatus) {
            ProductionStatus::MasterAssigned->value => 'new',
            ProductionStatus::Diagnosed->value, ProductionStatus::InWork->value => 'in_work',
            ProductionStatus::WaitingParts->value => 'waiting_parts',
            ProductionStatus::WorkCompleted->value, ProductionStatus::Completed->value => 'ready',
            default => $productionStatus,
        };
    }

    public function serviceTypeLabel(string $serviceType): string
    {
        return match ($serviceType) {
            OrderServiceType::Repair->value => 'Ремонт',
            OrderServiceType::Sharpening->value => 'Заточка',
            default => $serviceType,
        };
    }

    public function billingLabel(string $billingType): bool
    {
        return $billingType === OrderBillingType::Warranty->value;
    }
}
