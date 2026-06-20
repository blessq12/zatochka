<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class SiteSettingsCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $slug = 'site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?int $navigationSort = 7;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
