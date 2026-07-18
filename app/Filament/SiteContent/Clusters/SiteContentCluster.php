<?php

namespace App\Filament\SiteContent\Clusters;

use App\Filament\SiteContent\Pages\ManageSiteContent;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

final class SiteContentCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Сайт';

    protected static ?string $clusterBreadcrumb = 'Сайт';

    protected static ?string $slug = 'site';

    protected static ?int $navigationSort = 80;

    protected static bool $shouldRegisterSubNavigation = false;

    public function mount(): void
    {
        redirect(ManageSiteContent::getUrl());
    }
}
