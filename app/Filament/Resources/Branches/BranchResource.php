<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Clusters\CatalogCluster;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Infrastructure\Catalog\Persistence\Eloquent\BranchModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $model = BranchModel::class;

    protected static ?string $navigationLabel = 'Филиал';

    protected static ?string $slug = 'branches';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'филиал';

    protected static ?string $pluralModelLabel = 'Филиалы';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
