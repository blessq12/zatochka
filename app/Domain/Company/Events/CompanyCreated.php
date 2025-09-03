<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;

class CompanyCreated extends DomainEvent
{
    public function __construct(
        private readonly CompanyId $companyId,
        private readonly CompanyName $name,
        private readonly LegalName $legalName,
        private readonly INN $inn
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CompanyCreated';
    }

    public function eventData(): array
    {
        return [
            'company_id' => (string) $this->companyId,
            'name' => (string) $this->name,
            'legal_name' => (string) $this->legalName,
            'inn' => (string) $this->inn,
        ];
    }

    public function companyId(): CompanyId
    {
        return $this->companyId;
    }

    public function name(): CompanyName
    {
        return $this->name;
    }

    public function legalName(): LegalName
    {
        return $this->legalName;
    }

    public function inn(): INN
    {
        return $this->inn;
    }
}
