<?php

namespace App\Filament\Resources\CompanySettings;

use App\Filament\Clusters\CompanyCluster;
use App\Filament\Company\Schemas\CompanySettingsForm;
use App\Filament\Resources\CompanySettings\Pages\ManageCompanySettings;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanySettingsResource extends Resource
{
    protected static ?string $cluster = CompanyCluster::class;

    protected static ?string $model = CompanySettingModel::class;

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'настройка';

    protected static ?string $pluralModelLabel = 'Настройки';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function form(Schema $schema): Schema
    {
        return CompanySettingsForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCompanySettings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
