<?php

namespace App\Filament\Resources\CompanySettings;

use App\Filament\Clusters\CompanyCluster;
use App\Filament\Resources\CompanySettings\Pages\EditCompanySetting;
use App\Filament\Resources\CompanySettings\Pages\ListCompanySettings;
use App\Filament\Resources\CompanySettings\Schemas\CompanySettingForm;
use App\Filament\Resources\CompanySettings\Tables\CompanySettingsTable;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanySettingResource extends Resource
{
    private const KEYS = ['contacts', 'schedule', 'company'];

    protected static ?string $cluster = CompanyCluster::class;

    protected static ?string $model = CompanySettingModel::class;

    protected static ?string $navigationLabel = 'Настройки компании';

    protected static ?string $slug = 'company-settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'настройка';

    protected static ?string $pluralModelLabel = 'Настройки компании';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function form(Schema $schema): Schema
    {
        return CompanySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanySettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanySettings::route('/'),
            'edit' => EditCompanySetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('key', self::KEYS);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
