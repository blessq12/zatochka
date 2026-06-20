<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Clusters\ClientPortalCluster;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $cluster = ClientPortalCluster::class;

    protected static ?string $model = ClientModel::class;

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
