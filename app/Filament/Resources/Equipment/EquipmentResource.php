<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\Clusters\EquipmentCluster;
use App\Filament\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $cluster = EquipmentCluster::class;

    protected static ?string $model = EquipmentModel::class;

    protected static ?string $navigationLabel = 'Реестр';

    protected static ?string $slug = 'registry';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'оборудование';

    protected static ?string $pluralModelLabel = 'Оборудование';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipment::route('/'),
            'create' => CreateEquipment::route('/create'),
        ];
    }
}
