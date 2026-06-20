<?php

namespace App\Filament\Resources\Managers;

use App\Domain\Identity\Enum\UserRole;
use App\Filament\Clusters\IdentityCluster;
use App\Filament\Resources\Managers\Pages\CreateManager;
use App\Filament\Resources\Managers\Pages\EditManager;
use App\Filament\Resources\Managers\Pages\ListManagers;
use App\Filament\Resources\Managers\Schemas\ManagerForm;
use App\Filament\Resources\Managers\Tables\ManagersTable;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManagerResource extends Resource
{
    protected static ?string $cluster = IdentityCluster::class;

    protected static ?string $model = UserModel::class;

    protected static ?string $navigationLabel = 'Менеджеры';

    protected static ?string $slug = 'managers';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'менеджер';

    protected static ?string $pluralModelLabel = 'Менеджеры';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    public static function form(Schema $schema): Schema
    {
        return ManagerForm::configure($schema, isCreate: false);
    }

    public static function table(Table $table): Table
    {
        return ManagersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManagers::route('/'),
            'create' => CreateManager::route('/create'),
            'edit' => EditManager::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', UserRole::Manager);
    }
}
