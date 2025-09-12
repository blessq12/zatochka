<?php

namespace App\Domain\Order\Service;

use App\Domain\Order\Enum\OrderStatus;

class OrderStatusGroupingService
{
    /**
     * Получить статусы для менеджера
     */
    public function getManagerStatuses(): array
    {
        return OrderStatus::getManagerStatuses();
    }

    /**
     * Получить статусы для мастерской
     */
    public function getWorkshopStatuses(): array
    {
        return OrderStatus::getWorkshopStatuses();
    }

    /**
     * Получить финальные статусы
     */
    public function getFinalStatuses(): array
    {
        return OrderStatus::getFinalStatuses();
    }

    /**
     * Можно ли передать заказ в мастерскую
     */
    public function canTransferToWorkshop(OrderStatus $status): bool
    {
        return match ($status) {
            OrderStatus::NEW, OrderStatus::CONSULTATION => true,
            default => false,
        };
    }

    /**
     * Можно ли передать заказ менеджеру
     */
    public function canTransferToManager(OrderStatus $status): bool
    {
        return match ($status) {
            OrderStatus::DIAGNOSTIC, OrderStatus::IN_WORK, OrderStatus::WAITING_PARTS => true,
            default => false,
        };
    }

    /**
     * Получить следующий статус для передачи в мастерскую
     */
    public function getNextWorkshopStatus(OrderStatus $currentStatus): ?OrderStatus
    {
        return match ($currentStatus) {
            OrderStatus::NEW => OrderStatus::DIAGNOSTIC,
            OrderStatus::CONSULTATION => OrderStatus::DIAGNOSTIC,
            default => null,
        };
    }

    /**
     * Получить следующий статус для передачи менеджеру
     */
    public function getNextManagerStatus(OrderStatus $currentStatus): ?OrderStatus
    {
        return match ($currentStatus) {
            OrderStatus::DIAGNOSTIC => OrderStatus::READY,
            OrderStatus::IN_WORK => OrderStatus::READY,
            OrderStatus::WAITING_PARTS => OrderStatus::READY,
            default => null,
        };
    }

    /**
     * Получить статусы для выбора при передаче в мастерскую
     */
    public function getAvailableWorkshopStatuses(OrderStatus $currentStatus): array
    {
        if (!$this->canTransferToWorkshop($currentStatus)) {
            return [];
        }

        return [
            OrderStatus::DIAGNOSTIC,
            OrderStatus::IN_WORK,
        ];
    }

    /**
     * Получить статусы для выбора при передаче менеджеру
     */
    public function getAvailableManagerStatuses(OrderStatus $currentStatus): array
    {
        if (!$this->canTransferToManager($currentStatus)) {
            return [];
        }

        return [
            OrderStatus::READY,
            OrderStatus::WAITING_PARTS,
        ];
    }

    /**
     * Получить статусы для выбора при выдаче заказа
     */
    public function getAvailableIssueStatuses(OrderStatus $currentStatus): array
    {
        return match ($currentStatus) {
            OrderStatus::READY => [OrderStatus::ISSUED],
            default => [],
        };
    }

    /**
     * Получить статусы для отмены заказа
     */
    public function getAvailableCancelStatuses(OrderStatus $currentStatus): array
    {
        return match ($currentStatus) {
            OrderStatus::NEW, OrderStatus::CONSULTATION, OrderStatus::DIAGNOSTIC,
            OrderStatus::IN_WORK, OrderStatus::WAITING_PARTS => [OrderStatus::CANCELLED],
            default => [],
        };
    }

    /**
     * Получить все доступные переходы для текущего статуса
     */
    public function getAvailableTransitions(OrderStatus $currentStatus): array
    {
        $transitions = [];

        // Переходы в мастерскую
        if ($this->canTransferToWorkshop($currentStatus)) {
            $transitions['workshop'] = $this->getAvailableWorkshopStatuses($currentStatus);
        }

        // Переходы к менеджеру
        if ($this->canTransferToManager($currentStatus)) {
            $transitions['manager'] = $this->getAvailableManagerStatuses($currentStatus);
        }

        // Выдача заказа
        if (!empty($this->getAvailableIssueStatuses($currentStatus))) {
            $transitions['issue'] = $this->getAvailableIssueStatuses($currentStatus);
        }

        // Отмена заказа
        if (!empty($this->getAvailableCancelStatuses($currentStatus))) {
            $transitions['cancel'] = $this->getAvailableCancelStatuses($currentStatus);
        }

        return $transitions;
    }

    /**
     * Проверить, можно ли выполнить переход между статусами
     */
    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        $transitions = $this->getAvailableTransitions($from);

        foreach ($transitions as $type => $statuses) {
            if (in_array($to, $statuses)) {
                return true;
            }
        }

        return false;
    }
}
