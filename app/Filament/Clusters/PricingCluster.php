<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class PricingCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Прайс';

    protected static ?string $slug = 'pricing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 6;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
