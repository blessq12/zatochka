<?php

namespace App\Filament\Support;

use App\Filament\Clusters\SiteSettingsCluster;
use App\Infrastructure\SiteSettings\Persistence\Eloquent\SiteSettingModel;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractSiteSettingResource extends Resource
{
    protected static ?string $cluster = SiteSettingsCluster::class;

    protected static ?string $model = SiteSettingModel::class;

    protected static ?string $modelLabel = 'настройка';

    abstract public static function settingKey(): string;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('key', static::settingKey());
    }

    public static function getNavigationUrl(array $parameters = [], bool $isAbsolute = true, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        return static::getUrl('index', $parameters, $isAbsolute, null, $tenant);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function resolveSettingRecord(): SiteSettingModel
    {
        /** @var SiteSettingModel $record */
        $record = static::getModel()::query()
            ->where('key', static::settingKey())
            ->firstOrFail();

        return $record;
    }
}
