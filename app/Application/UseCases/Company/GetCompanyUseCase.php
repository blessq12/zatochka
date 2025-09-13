<?php

namespace App\Application\UseCases\Company;

use App\Domain\Company\Entity\Company;

class GetCompanyUseCase extends BaseCompanyUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID компании обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID компании должен быть числом');
        }

        return $this;
    }

    public function execute(): ?Company
    {
        return $this->companyRepository->get($this->data['id']);
    }
}
