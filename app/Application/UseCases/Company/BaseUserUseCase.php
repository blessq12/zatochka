<?php

namespace App\Application\UseCases\Company;

use App\Application\UseCases\Company\CompanyUseCaseInterface;

abstract class BaseUserUseCase implements CompanyUseCaseInterface
{
    protected array $data;

    // Все репозитории и мапперы Company домена
    protected \App\Domain\Company\Repository\UserRepository $userRepository;
    protected \App\Domain\Company\Repository\CompanyRepository $companyRepository;

    protected \App\Domain\Company\Mapper\UserMapper $userMapper;
    protected \App\Domain\Company\Mapper\CompanyMapper $companyMapper;

    public function __construct()
    {
        // Подтягиваем ВСЕ зависимости Company домена
        $this->userRepository = app(\App\Domain\Company\Repository\UserRepository::class);
        $this->companyRepository = app(\App\Domain\Company\Repository\CompanyRepository::class);

        $this->userMapper = app(\App\Domain\Company\Mapper\UserMapper::class);
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
