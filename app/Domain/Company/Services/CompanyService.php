<?php

namespace App\Domain\Company\Services;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use App\Domain\Company\Interfaces\CompanyRepositoryInterface;
use App\Domain\Company\Interfaces\BranchRepositoryInterface;
use App\Domain\Company\Events\CompanyCreated;
use App\Domain\Company\Events\CompanyActivated;
use App\Domain\Company\Events\CompanyDeactivated;
use App\Domain\Shared\Events\EventBusInterface;

class CompanyService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companyRepository,
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly EventBusInterface $eventBus
    ) {}

    public function createCompany(
        CompanyName $name,
        LegalName $legalName,
        INN $inn,
        string $legalAddress,
        ?string $description = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $bankName = null,
        ?string $bankBik = null,
        ?string $bankAccount = null,
        ?string $bankCorAccount = null,
        ?string $logoPath = null,
        array $additionalData = []
    ): Company {
        // Проверяем уникальность ИНН
        if ($this->companyRepository->existsByInn($inn)) {
            throw new \InvalidArgumentException('Company with this INN already exists');
        }

        $company = Company::create(
            $name,
            $legalName,
            $inn,
            $legalAddress,
            $description,
            $website,
            $phone,
            $email,
            $bankName,
            $bankBik,
            $bankAccount,
            $bankCorAccount,
            $logoPath,
            $additionalData
        );

        $this->companyRepository->save($company);

        // Публикуем события
        $events = $company->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $company;
    }

    public function updateCompany(
        int $companyId,
        ?CompanyName $name = null,
        ?LegalName $legalName = null,
        ?string $description = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $bankName = null,
        ?string $bankBik = null,
        ?string $bankAccount = null,
        ?string $bankCorAccount = null,
        ?string $logoPath = null,
        ?array $additionalData = null
    ): Company {
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new \InvalidArgumentException('Company not found');
        }

        if ($name) {
            $company->updateName($name);
        }
        if ($legalName) {
            $company->updateLegalName($legalName);
        }
        if ($description !== null) {
            $company->updateDescription($description);
        }
        if ($website !== null || $phone !== null || $email !== null) {
            $company->updateContactInfo($phone, $email, $website);
        }
        if ($bankName !== null || $bankBik !== null || $bankAccount !== null || $bankCorAccount !== null) {
            $company->updateBankInfo($bankName, $bankBik, $bankAccount, $bankCorAccount);
        }
        if ($logoPath !== null) {
            $company->setLogoPath($logoPath);
        }
        if ($additionalData !== null) {
            $company->updateAdditionalData($additionalData);
        }

        $this->companyRepository->save($company);

        // Публикуем события
        $events = $company->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $company;
    }

    public function activateCompany(int $companyId): Company
    {
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new \InvalidArgumentException('Company not found');
        }

        $company->activate();
        $this->companyRepository->save($company);

        // Публикуем события
        $events = $company->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $company;
    }

    public function deactivateCompany(int $companyId): Company
    {
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new \InvalidArgumentException('Company not found');
        }

        $company->deactivate();
        $this->companyRepository->save($company);

        // Публикуем события
        $events = $company->pullEvents();
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        return $company;
    }

    public function deleteCompany(int $companyId): void
    {
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new \InvalidArgumentException('Company not found');
        }

        // Проверяем, можно ли удалить компанию
        if (!$company->canBeDeleted()) {
            throw new \InvalidArgumentException('Company cannot be deleted');
        }

        // Проверяем наличие активных филиалов
        $activeBranches = $this->branchRepository->findActiveByCompanyId($companyId);
        if (!empty($activeBranches)) {
            throw new \InvalidArgumentException('Cannot delete company with active branches');
        }

        $company->markDeleted();
        $this->companyRepository->save($company);
    }

    public function getCompanyById(int $companyId): ?Company
    {
        return $this->companyRepository->findById($companyId);
    }

    public function getCompanyByInn(INN $inn): ?Company
    {
        return $this->companyRepository->findByInn($inn);
    }

    public function getAllActiveCompanies(): array
    {
        return $this->companyRepository->findActive();
    }

    public function getAllCompanies(): array
    {
        return $this->companyRepository->findAll();
    }

    public function companyExists(int $companyId): bool
    {
        return $this->companyRepository->exists($companyId);
    }

    public function companyExistsByInn(INN $inn): bool
    {
        return $this->companyRepository->existsByInn($inn);
    }
}
