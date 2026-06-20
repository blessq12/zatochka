<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class CompanyCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Сайт и компания';

    protected static ?string $clusterBreadcrumb = 'Сайт и компания';

    protected static ?string $slug = 'company';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 5;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
