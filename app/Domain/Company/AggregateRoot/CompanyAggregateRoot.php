<?php

namespace App\Domain\Company\AggregateRoot;

use App\Domain\Company\Event\CompanyCreated;
use App\Domain\Company\Event\CompanyUpdated;
use App\Domain\Company\Event\CompanyBankDetailsUpdated;
use App\Domain\Company\Event\CompanyActivated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class CompanyAggregateRoot extends AggregateRoot
{
    public function createCompany(
        int $companyId,
        string $name,
        string $legalName,
        string $inn,
        ?string $kpp,
        ?string $ogrn,
        string $legalAddress,
        ?string $description,
        ?string $website,
        ?string $phone,
        ?string $email,
        int $createdBy
    ): self {
        $this->recordThat(new CompanyCreated(
            companyId: $companyId,
            name: $name,
            legalName: $legalName,
            inn: $inn,
            kpp: $kpp,
            ogrn: $ogrn,
            legalAddress: $legalAddress,
            description: $description,
            website: $website,
            phone: $phone,
            email: $email,
            createdBy: $createdBy
        ));

        return $this;
    }

    public function updateCompany(
        int $companyId,
        string $name,
        string $legalName,
        ?string $description,
        ?string $website,
        ?string $phone,
        ?string $email,
        int $updatedBy
    ): self {
        $this->recordThat(new CompanyUpdated(
            companyId: $companyId,
            name: $name,
            legalName: $legalName,
            description: $description,
            website: $website,
            phone: $phone,
            email: $email,
            updatedBy: $updatedBy
        ));

        return $this;
    }

    public function updateBankDetails(
        int $companyId,
        ?string $bankName,
        ?string $bankBik,
        ?string $bankAccount,
        ?string $bankCorAccount,
        int $updatedBy
    ): self {
        $this->recordThat(new CompanyBankDetailsUpdated(
            companyId: $companyId,
            bankName: $bankName,
            bankBik: $bankBik,
            bankAccount: $bankAccount,
            bankCorAccount: $bankCorAccount,
            updatedBy: $updatedBy
        ));

        return $this;
    }

    public function activateCompany(int $companyId, int $activatedBy): self
    {
        $this->recordThat(new CompanyActivated(
            companyId: $companyId,
            activatedBy: $activatedBy
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
