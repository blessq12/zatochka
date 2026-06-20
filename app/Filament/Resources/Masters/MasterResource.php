<?php

namespace App\Filament\Resources\Masters;

use App\Filament\Clusters\IdentityCluster;
use App\Filament\Resources\Masters\Pages\CreateMaster;
use App\Filament\Resources\Masters\Pages\EditMaster;
use App\Filament\Resources\Masters\Pages\ListMasters;
use App\Filament\Resources\Masters\Schemas\MasterForm;
use App\Filament\Resources\Masters\Tables\MastersTable;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterResource extends Resource
{
    protected static ?string $cluster = IdentityCluster::class;

    protected static ?string $model = UserModel::class;

    protected static ?string $navigationLabel = 'Мастера';

    protected static ?string $slug = 'masters';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'мастер';

    protected static ?string $pluralModelLabel = 'Мастера';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    public static function form(Schema $schema): Schema
    {
        return MasterForm::configure($schema, isCreate: false);
    }

    public static function table(Table $table): Table
    {
        return MastersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasters::route('/'),
            'create' => CreateMaster::route('/create'),
            'edit' => EditMaster::route('/{record}/edit'),
        ];
    }
}
