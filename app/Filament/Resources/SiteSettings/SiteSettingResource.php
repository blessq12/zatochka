<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Clusters\CatalogCluster;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Filament\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Infrastructure\Catalog\Persistence\Eloquent\SiteSettingModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $model = SiteSettingModel::class;

    protected static ?string $navigationLabel = 'Контент сайта';

    protected static ?string $slug = 'site-settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'настройка';

    protected static ?string $pluralModelLabel = 'Контент сайта';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function form(Schema $schema): Schema
    {
        return SiteSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
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
