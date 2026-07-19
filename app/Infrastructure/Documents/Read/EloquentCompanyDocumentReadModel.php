<?php

namespace App\Infrastructure\Documents\Read;

use App\Application\Documents\DTO\CompanyDocumentSnapshot;
use App\Application\Documents\Port\CompanyDocumentReadPort;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use App\Shared\Domain\DomainException;

final class EloquentCompanyDocumentReadModel implements CompanyDocumentReadPort
{
    public function get(): CompanyDocumentSnapshot
    {
        $company = CompanyProfileModel::query()->find(1);
        $contacts = SiteContactsModel::query()->find(1);

        if ($company === null) {
            throw new DomainException('Company profile is not configured.');
        }

        $name = (string) $company->owner_name;
        $inn = (string) $company->inn;
        $ogrn = (string) $company->ogrn;
        $legalAddress = (string) $company->legal_address;
        $actualAddress = (string) $company->actual_address;
        $phone = (string) ($contacts?->phone ?? '');
        $address = (string) ($contacts?->address_main ?? '');
        $branchAddress = $address !== '' ? $address : $actualAddress;

        $headerParts = array_filter([
            $name,
            $inn !== '' ? 'ИНН '.$inn : null,
            $ogrn !== '' ? 'ОГРН '.$ogrn : null,
            $legalAddress !== '' ? $legalAddress : null,
            $phone !== '' ? $phone : null,
        ]);

        return new CompanyDocumentSnapshot([
            'company_name' => $name,
            'company.owner_name' => $name,
            'company_inn' => $inn,
            'company_ogrn' => $ogrn,
            'company_legal_address' => $legalAddress,
            'company_actual_address' => $actualAddress,
            'company_phone' => $phone,
            'company_address' => $branchAddress,
            'company.header' => htmlspecialchars(implode(' · ', $headerParts), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'branch.name' => $name,
            'branch.address' => $branchAddress,
            'branch.phone' => $phone,
            'logo' => $this->logoHtml($name),
        ]);
    }

    private function logoHtml(string $fallbackName): string
    {
        $path = public_path('images/logo.png');

        if (is_file($path)) {
            $bytes = file_get_contents($path);

            if ($bytes !== false && $bytes !== '') {
                $src = 'data:image/png;base64,'.base64_encode($bytes);

                return '<div class="logo"><img src="'.$src.'" alt="" height="28"></div>';
            }
        }

        if ($fallbackName === '') {
            return '';
        }

        return '<div class="logo">'.htmlspecialchars($fallbackName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</div>';
    }
}
