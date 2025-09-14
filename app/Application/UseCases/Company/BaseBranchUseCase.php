<?php

namespace App\Application\UseCases\Company;

use App\Application\UseCases\Company\CompanyUseCaseInterface;

abstract class BaseBranchUseCase implements CompanyUseCaseInterface
{
    protected array $data;

    // Все репозитории и мапперы Company домена
    protected \App\Domain\Company\Repository\BranchRepository $branchRepository;
    protected \App\Domain\Company\Repository\CompanyRepository $companyRepository;

    protected \App\Domain\Company\Mapper\BranchMapper $branchMapper;
    protected \App\Domain\Company\Mapper\CompanyMapper $companyMapper;

    public function __construct()
    {
        // Подтягиваем ВСЕ зависимости Company домена
        $this->branchRepository = app(\App\Domain\Company\Repository\BranchRepository::class);
        $this->companyRepository = app(\App\Domain\Company\Repository\CompanyRepository::class);

        $this->branchMapper = app(\App\Domain\Company\Mapper\BranchMapper::class);
        $this->companyMapper = app(\App\Domain\Company\Mapper\CompanyMapper::class);
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

    abstract protected function validateSpecificData(): self;
    abstract public function execute(): mixed;
}
