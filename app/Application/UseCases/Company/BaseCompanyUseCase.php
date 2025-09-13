<?php

namespace App\Application\UseCases\Company;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Company\Repository\CompanyRepository;

abstract class BaseCompanyUseCase implements CompanyUseCaseInterface
{
    protected array $data;
    protected CompanyRepository $companyRepository;

    public function __construct()
    {
        $this->companyRepository = app(CompanyRepository::class);
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract public function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
