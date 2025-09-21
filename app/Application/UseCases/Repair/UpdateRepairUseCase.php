<?php

namespace App\Application\UseCases\Repair;

use App\Domain\Repair\Entity\Repair;
use App\Domain\Repair\Enum\RepairStatus;
use App\Domain\Company\Entity\User;

class UpdateRepairUseCase extends BaseRepairUseCase
{
    protected function validateSpecificData(): self
    {
        // Валидация ID ремонта
        if (empty($this->data['repair_id'])) {
            throw new \InvalidArgumentException('Repair ID is required');
        }

        $repair = $this->repairRepository->get($this->data['repair_id']);
        if (!$repair) {
            throw new \InvalidArgumentException('Repair not found');
        }

        // Валидация статуса
        if (!empty($this->data['status'])) {
            $validStatuses = RepairStatus::getAll();
            if (!in_array($this->data['status'], $validStatuses)) {
                throw new \InvalidArgumentException('Invalid repair status');
            }

            // Бизнес-правила смены статуса
            $this->validateStatusTransition($repair, $this->data['status']);
        }

        // Валидация мастера (если указан)
        if (!empty($this->data['master_id'])) {
            $master = $this->userRepository->get($this->data['master_id']);
            if (!$master) {
                throw new \InvalidArgumentException('Master not found');
            }
        }

        return $this;
    }

    public function execute(): Repair
    {
        $repair = $this->repairRepository->get($this->data['repair_id']);

        // Подготовка данных для обновления
        $updateData = [];

        if (isset($this->data['master_id'])) {
            $updateData['master_id'] = $this->data['master_id'];
        }

        if (isset($this->data['status'])) {
            $updateData['status'] = $this->data['status'];

            // Автоматически устанавливаем даты при смене статуса
            if ($this->data['status'] === RepairStatus::IN_PROGRESS->value && !$repair->getStartedAt()) {
                $updateData['started_at'] = new \DateTime();
            }

            if ($this->data['status'] === RepairStatus::COMPLETED->value) {
                $updateData['completed_at'] = new \DateTime();
            }
        }

        if (isset($this->data['description'])) {
            $updateData['description'] = $this->data['description'];
        }

        if (isset($this->data['diagnosis'])) {
            $updateData['diagnosis'] = $this->data['diagnosis'];
        }

        if (isset($this->data['work_performed'])) {
            $updateData['work_performed'] = $this->data['work_performed'];
        }

        if (isset($this->data['notes'])) {
            $updateData['notes'] = $this->data['notes'];
        }

        if (isset($this->data['estimated_completion'])) {
            $updateData['estimated_completion'] = $this->data['estimated_completion'];
        }

        if (isset($this->data['parts_used'])) {
            $updateData['parts_used'] = $this->data['parts_used'];
        }

        if (isset($this->data['additional_data'])) {
            $updateData['additional_data'] = $this->data['additional_data'];
        }

        return $this->repairRepository->update($repair, $updateData);
    }

    private function validateStatusTransition(Repair $repair, string $newStatus): void
    {
        $currentStatus = $repair->getStatus();

        // Нельзя изменить статус завершенного или отмененного ремонта
        if (in_array($currentStatus, [RepairStatus::COMPLETED->value, RepairStatus::CANCELLED->value])) {
            throw new \InvalidArgumentException('Cannot change status of completed or cancelled repair');
        }

        // Бизнес-правила переходов статусов
        $allowedTransitions = [
            RepairStatus::PENDING->value => [RepairStatus::DIAGNOSIS->value, RepairStatus::CANCELLED->value],
            RepairStatus::DIAGNOSIS->value => [RepairStatus::IN_PROGRESS->value, RepairStatus::WAITING_PARTS->value, RepairStatus::CANCELLED->value],
            RepairStatus::IN_PROGRESS->value => [RepairStatus::WAITING_PARTS->value, RepairStatus::TESTING->value, RepairStatus::COMPLETED->value, RepairStatus::CANCELLED->value],
            RepairStatus::WAITING_PARTS->value => [RepairStatus::IN_PROGRESS->value, RepairStatus::CANCELLED->value],
            RepairStatus::TESTING->value => [RepairStatus::COMPLETED->value, RepairStatus::IN_PROGRESS->value, RepairStatus::CANCELLED->value],
        ];

        if (
            !isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])
        ) {
            throw new \InvalidArgumentException("Invalid status transition from {$currentStatus} to {$newStatus}");
        }
    }
}
