<?php

namespace App\Filament\Resources\SiteContent;

use App\Filament\Clusters\CompanyCluster;
use App\Filament\Company\Schemas\SiteContentForm;
use App\Filament\Resources\SiteContent\Pages\ManageSiteContent;
use App\Infrastructure\Company\Persistence\Eloquent\SiteContentModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteContentResource extends Resource
{
    protected static ?string $cluster = CompanyCluster::class;

    protected static ?string $model = SiteContentModel::class;

    protected static ?string $navigationLabel = 'Контент';

    protected static ?string $slug = 'site-content';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'контент';

    protected static ?string $pluralModelLabel = 'Контент';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function form(Schema $schema): Schema
    {
        return SiteContentForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteContent::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
