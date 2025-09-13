<?php

namespace App\Application\UseCases\Company;

use App\Domain\Company\AggregateRoot\CompanyAggregateRoot;
use App\Domain\Company\Entity\Company;

class CreateCompanyUseCase extends BaseCompanyUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название компании обязательно');
        }

        if (empty($this->data['legal_name'])) {
            throw new \InvalidArgumentException('Юридическое название обязательно');
        }

        if (empty($this->data['inn'])) {
            throw new \InvalidArgumentException('ИНН обязателен');
        }

        if (empty($this->data['legal_address'])) {
            throw new \InvalidArgumentException('Юридический адрес обязателен');
        }

        // Проверяем уникальность ИНН
        if ($this->companyRepository->existsByInn($this->data['inn'])) {
            throw new \InvalidArgumentException('Компания с таким ИНН уже существует');
        }

        // Проверяем уникальность названия
        if ($this->companyRepository->existsByName($this->data['name'])) {
            throw new \InvalidArgumentException('Компания с таким названием уже существует');
        }

        return $this;
    }

    public function execute(): Company
    {
        // Создаем через Event Sourcing
        $aggregate = CompanyAggregateRoot::create();
        $aggregate->createCompany(
            companyId: 0, // Будет установлен после сохранения
            name: $this->data['name'],
            legalName: $this->data['legal_name'],
            inn: $this->data['inn'],
            kpp: $this->data['kpp'] ?? null,
            ogrn: $this->data['ogrn'] ?? null,
            legalAddress: $this->data['legal_address'],
            description: $this->data['description'] ?? null,
            website: $this->data['website'] ?? null,
            phone: $this->data['phone'] ?? null,
            email: $this->data['email'] ?? null,
            createdBy: auth()->id() ?? 1
        );

        $aggregate->persist();

        // Возвращаем созданную сущность
        return $this->companyRepository->create([
            'name' => $this->data['name'],
            'legal_name' => $this->data['legal_name'],
            'inn' => $this->data['inn'],
            'kpp' => $this->data['kpp'] ?? null,
            'ogrn' => $this->data['ogrn'] ?? null,
            'legal_address' => $this->data['legal_address'],
            'description' => $this->data['description'] ?? null,
            'website' => $this->data['website'] ?? null,
            'phone' => $this->data['phone'] ?? null,
            'email' => $this->data['email'] ?? null,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }
}
