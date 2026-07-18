<?php

namespace App\Filament\SiteContent\Resources;

use App\Domain\SiteContent\VO\PricePrefix;
use App\Domain\SiteContent\VO\ServicePriceCategory;
use App\Filament\SiteContent\Resources\ServicePriceListResource\Pages\ListServicePrices;
use App\Filament\Support\CatalogResource;
use App\Infrastructure\SiteContent\Model\ServicePriceModel;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServicePriceListResource extends CatalogResource
{
    protected static ?string $model = ServicePriceModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Прайс услуг';

    protected static ?string $modelLabel = 'Позиция прайса';

    protected static ?string $pluralModelLabel = 'Прайс услуг';

    protected static ?string $slug = 'service-price-list';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 81;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category')
                ->label('Категория')
                ->options(ServicePriceCategory::options())
                ->required()
                ->native(false),
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Цена')
                ->required()
                ->maxLength(32),
            Select::make('prefix')
                ->label('Префикс')
                ->options(PricePrefix::options())
                ->nullable()
                ->native(false),
            Textarea::make('description')
                ->label('Описание')
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('prefix')
                    ->label('Префикс')
                    ->formatStateUsing(fn (?string $state): string => PricePrefix::fromNullable($state)?->label() ?? '—'),
                TextColumn::make('price')
                    ->label('Цена'),
            ])
            ->defaultSort('id')
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Редактирование позиции прайса'),
                DeleteAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicePrices::route('/'),
        ];
    }

    public static function canView(Model $record): bool
    {
        return false;
    }
}
