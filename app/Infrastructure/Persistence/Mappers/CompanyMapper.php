<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use App\Models\Company as CompanyModel;

class CompanyMapper
{
    public function toDomain(CompanyModel $model): Company
    {
        // Обрабатываем additional_data - если это JSON строка, декодируем в массив
        $additionalData = [];
        if ($model->additional_data) {
            if (is_string($model->additional_data)) {
                $decoded = json_decode($model->additional_data, true);
                $additionalData = is_array($decoded) ? $decoded : [];
            } elseif (is_array($model->additional_data)) {
                $additionalData = $model->additional_data;
            }
        }

        return Company::reconstitute(
            $model->id,
            CompanyName::fromString($model->name),
            LegalName::fromString($model->legal_name),
            INN::fromString($model->inn),
            $model->kpp,
            $model->ogrn,
            $model->legal_address,
            $model->description,
            $model->website,
            $model->phone,
            $model->email,
            $model->bank_name,
            $model->bank_bik,
            $model->bank_account,
            $model->bank_cor_account,
            $model->logo_path,
            $additionalData,
            $model->is_active,
            $model->is_deleted,
            \DateTimeImmutable::createFromInterface($model->created_at),
            \DateTimeImmutable::createFromInterface($model->updated_at)
        );
    }

    public function toEloquent(Company $company): CompanyModel
    {
        $model = new CompanyModel();

        // Если ID больше 0, значит это существующая компания
        if ($company->id() > 0) {
            $model->id = $company->id();
            $model->exists = true; // Указываем Laravel, что это существующая запись
        }
        // Если ID = 0, Laravel сам сгенерирует автоинкремент

        $model->name = $company->name()->value();
        $model->legal_name = $company->legalName()->value();
        $model->inn = $company->inn()->value();
        $model->kpp = $company->kpp();
        $model->ogrn = $company->ogrn();
        $model->legal_address = $company->legalAddress();
        $model->description = $company->description();
        $model->website = $company->website();
        $model->phone = $company->phone();
        $model->email = $company->email();
        $model->bank_name = $company->bankName();
        $model->bank_bik = $company->bankBik();
        $model->bank_account = $company->bankAccount();
        $model->bank_cor_account = $company->bankCorAccount();
        $model->logo_path = $company->logoPath();

        // Сохраняем additional_data как JSON строку
        $additionalData = $company->additionalData();
        $model->additional_data = !empty($additionalData) ? json_encode($additionalData) : null;

        $model->is_active = $company->isActive();
        $model->is_deleted = $company->isDeleted();
        $model->created_at = $company->createdAt();
        $model->updated_at = $company->updatedAt();

        return $model;
    }
}
