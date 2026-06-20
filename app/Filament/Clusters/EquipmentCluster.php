<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class EquipmentCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Оборудование';

    protected static ?string $clusterBreadcrumb = 'Оборудование';

    protected static ?string $slug = 'equipment';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 3;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
