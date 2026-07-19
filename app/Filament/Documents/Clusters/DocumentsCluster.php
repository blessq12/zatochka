<?php

namespace App\Filament\Documents\Clusters;

use App\Filament\Documents\Pages\ManageDocuments;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

final class DocumentsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Документы';

    protected static ?string $clusterBreadcrumb = 'Документы';

    protected static ?string $slug = 'documents';

    protected static ?int $navigationSort = 85;

    protected static bool $shouldRegisterSubNavigation = false;

    public function mount(): void
    {
        redirect(ManageDocuments::getUrl());
    }
}
