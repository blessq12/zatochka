<?php

namespace App\Domain\Company\Services;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\ValueObjects\BranchId;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use App\Domain\Company\Interfaces\BranchRepositoryInterface;
use App\Domain\Company\Interfaces\CompanyRepositoryInterface;
use App\Domain\Company\Events\BranchCreated;
use App\Domain\Company\Events\BranchActivated;
use App\Domain\Company\Events\BranchDeactivated;
use App\Domain\Company\Events\BranchSetAsMain;
use App\Domain\Shared\Events\EventBusInterface;

class BranchService
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly CompanyRepositoryInterface $companyRepository,
        private readonly EventBusInterface $eventBus
    ) {}

    public function createBranch(
        CompanyId $companyId,
        string $name,
        BranchCode $code,
        string $address,
        ?string $phone = null,
        ?string $email = null,
        WorkingSchedule $workingSchedule = null,
        ?string $openingTime = null,
        ?string $closingTime = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $description = null,
        array $additionalData = []
    ): Branch {
        // Проверяем существование компании
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new \InvalidArgumentException('Company not found');
        }

        // Проверяем уникальность кода филиала
        if ($this->branchRepository->existsByCode($code)) {
            throw new \InvalidArgumentException('Branch with this code already exists');
        }

        $branchId = BranchId::generate();
        
        $branch = Branch::create(
            $branchId,
            $companyId,
            $name,
            $code,
            $address,
            $phone,
            $email,
            $workingSchedule,
            $openingTime,
            $closingTime,
            $latitude,
            $longitude,
            $description,
            $additionalData
        );

        $this->branchRepository->save($branch);

        // Публикуем события
        $events = $branch->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $branch;
    }

    public function updateBranch(
        BranchId $branchId,
        ?string $name = null,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
        ?WorkingSchedule $workingSchedule = null,
        ?string $openingTime = null,
        ?string $closingTime = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $description = null,
        ?array $additionalData = null
    ): Branch {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        if ($name !== null) {
            $branch->updateName($name);
        }
        if ($address !== null) {
            $branch->updateAddress($address);
        }
        if ($phone !== null || $email !== null) {
            $branch->updateContactInfo($phone, $email);
        }
        if ($workingSchedule !== null) {
            $branch->updateWorkingSchedule($workingSchedule);
        }
        if ($openingTime !== null || $closingTime !== null) {
            // Обновляем время открытия/закрытия через дополнительные данные
            $currentData = $branch->additionalData();
            $currentData['opening_time'] = $openingTime;
            $currentData['closing_time'] = $closingTime;
            $branch->updateAdditionalData($currentData);
        }
        if ($latitude !== null || $longitude !== null) {
            $branch->updateLocation($latitude, $longitude);
        }
        if ($description !== null) {
            $branch->updateDescription($description);
        }
        if ($additionalData !== null) {
            $branch->updateAdditionalData($additionalData);
        }

        $this->branchRepository->save($branch);

        // Публикуем события
        $events = $branch->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $branch;
    }

    public function activateBranch(BranchId $branchId): Branch
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        $branch->activate();
        $this->branchRepository->save($branch);

        // Публикуем события
        $events = $branch->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $branch;
    }

    public function deactivateBranch(BranchId $branchId): Branch
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        $branch->deactivate();
        $this->branchRepository->save($branch);

        // Публикуем события
        $events = $branch->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $branch;
    }

    public function setBranchAsMain(BranchId $branchId): Branch
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        // Снимаем флаг главного с других филиалов компании
        $companyBranches = $this->branchRepository->findByCompanyId($branch->companyId());
        foreach ($companyBranches as $companyBranch) {
            if ($companyBranch->isMain()) {
                $companyBranch->unsetAsMain();
                $this->branchRepository->save($companyBranch);
            }
        }

        $branch->setAsMain();
        $this->branchRepository->save($branch);

        // Публикуем события
        $events = $branch->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $branch;
    }

    public function deleteBranch(BranchId $branchId): void
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        // Проверяем, можно ли удалить филиал
        if (!$branch->canBeDeleted()) {
            throw new \InvalidArgumentException('Branch cannot be deleted');
        }

        $branch->markDeleted();
        $this->branchRepository->save($branch);
    }

    public function getBranchById(BranchId $branchId): ?Branch
    {
        return $this->branchRepository->findById($branchId);
    }

    public function getBranchByCode(BranchCode $code): ?Branch
    {
        return $this->branchRepository->findByCode($code);
    }

    public function getBranchesByCompanyId(CompanyId $companyId): array
    {
        return $this->branchRepository->findByCompanyId($companyId);
    }

    public function getActiveBranchesByCompanyId(CompanyId $companyId): array
    {
        return $this->branchRepository->findActiveByCompanyId($companyId);
    }

    public function getMainBranchByCompanyId(CompanyId $companyId): ?Branch
    {
        return $this->branchRepository->findMainByCompanyId($companyId);
    }

    public function getAllActiveBranches(): array
    {
        return $this->branchRepository->findActive();
    }

    public function getAllBranches(): array
    {
        return $this->branchRepository->findAll();
    }

    public function branchExists(BranchId $branchId): bool
    {
        return $this->branchRepository->exists($branchId);
    }

    public function branchExistsByCode(BranchCode $code): bool
    {
        return $this->branchRepository->existsByCode($code);
    }

    public function countBranchesByCompanyId(CompanyId $companyId): int
    {
        return $this->branchRepository->countByCompanyId($companyId);
    }

    // Методы для работы с расписанием
    public function updateBranchWorkingSchedule(BranchId $branchId, WorkingSchedule $workingSchedule): Branch
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        $branch->updateWorkingSchedule($workingSchedule);
        $this->branchRepository->save($branch);

        return $branch;
    }

    public function isBranchWorkingNow(BranchId $branchId): bool
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        return $branch->isWorkingNow();
    }

    public function isBranchWorkingToday(BranchId $branchId): bool
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        return $branch->isWorkingToday();
    }

    public function getBranchWorkingSchedule(BranchId $branchId): WorkingSchedule
    {
        $branch = $this->branchRepository->findById($branchId);
        if (!$branch) {
            throw new \InvalidArgumentException('Branch not found');
        }

        return $branch->workingSchedule();
    }
}
