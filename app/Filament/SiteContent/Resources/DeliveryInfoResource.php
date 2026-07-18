<?php

namespace App\Filament\SiteContent\Resources;

use App\Filament\SiteContent\Resources\DeliveryInfoResource\Pages\EditDeliveryInfo;
use App\Filament\SiteContent\Resources\DeliveryInfoResource\Pages\ListDeliveryInfos;
use App\Filament\Support\DomainResource;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DeliveryInfoResource extends DomainResource
{
    protected static ?string $model = DeliveryInfoModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Доставка';

    protected static ?string $modelLabel = 'Доставка';

    protected static ?string $pluralModelLabel = 'Доставка';

    protected static ?int $navigationSort = 30;

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('free_conditions')
                ->label('Условия')
                ->simple(
                    TextInput::make('condition')
                        ->label('Условие')
                        ->required(),
                )
                ->default([]),
            Repeater::make('advantages')
                ->label('Преимущества')
                ->schema([
                    TextInput::make('title')->label('Заголовок')->required(),
                    Textarea::make('description')->label('Описание')->required()->rows(2),
                ])
                ->default([]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryInfos::route('/'),
            'edit' => EditDeliveryInfo::route('/{record}/edit'),
        ];
    }
}
