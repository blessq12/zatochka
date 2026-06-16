<?php

namespace App\Filament\Resources\SiteLeads;

use App\Filament\Resources\SiteLeads\Pages\ListSiteLeads;
use App\Filament\Resources\SiteLeads\Tables\SiteLeadsTable;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiteLeadResource extends Resource
{
    protected static ?string $model = SiteLeadModel::class;

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    public static function table(Table $table): Table
    {
        return SiteLeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteLeads::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('converted', false);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
