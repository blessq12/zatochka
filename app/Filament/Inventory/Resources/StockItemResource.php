<?php

namespace App\Filament\Inventory\Resources;

use App\Application\Inventory\Command\ChangeStockCommand;
use App\Application\Inventory\Command\ChangeStockHandler;
use App\Application\Inventory\Command\OpenStockItemCommand;
use App\Application\Inventory\Command\OpenStockItemHandler;
use App\Application\Inventory\Command\ReceiveMaterialCommand;
use App\Application\Inventory\Command\ReceiveMaterialHandler;
use App\Application\Inventory\Command\WriteOffMaterialCommand;
use App\Application\Inventory\Command\WriteOffMaterialHandler;
use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Filament\Inventory\Resources\StockItemResource\Pages\ListStockItems;
use App\Filament\Inventory\Resources\StockItemResource\Pages\ViewStockItem;
use App\Filament\Inventory\Resources\StockItemResource\RelationManagers\WarehouseMovementsRelationManager;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StockItemResource extends DomainResource
{
    protected static ?string $model = StockItemModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Склад';

    protected static ?string $navigationLabel = 'Остатки';

    protected static ?string $modelLabel = 'Позиция склада';

    protected static ?string $pluralModelLabel = 'Остатки';

    protected static ?int $navigationSort = 10;

    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof StockItemModel) {
            return static::getModelLabel();
        }

        $record->loadMissing('material');

        $name = $record->material?->name;

        return filled($name) ? (string) $name : static::getModelLabel();
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('material');
    }

    public static function getRelations(): array
    {
        return [
            WarehouseMovementsRelationManager::class,
        ];
    }

    /** @return array<string, string> */
    public static function categoryOptions(): array
    {
        return [
            StockCategory::Consumable->value => 'Расходные материалы',
            StockCategory::SparePart->value => 'Запчасти',
        ];
    }

    /** @return array<string, string> */
    public static function unitOptions(): array
    {
        return [
            UnitOfMeasure::Piece->value => 'шт',
            UnitOfMeasure::Pack->value => 'упак',
            UnitOfMeasure::Set->value => 'комплект',
            UnitOfMeasure::Pair->value => 'пара',
            UnitOfMeasure::Kilogram->value => 'кг',
            UnitOfMeasure::Gram->value => 'г',
            UnitOfMeasure::Liter->value => 'л',
            UnitOfMeasure::Milliliter->value => 'мл',
            UnitOfMeasure::Meter->value => 'м',
            UnitOfMeasure::Centimeter->value => 'см',
            UnitOfMeasure::Roll->value => 'рулон',
            UnitOfMeasure::Bottle->value => 'флакон',
            UnitOfMeasure::Can->value => 'банка',
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('material.name')
                ->label('Название'),
            TextEntry::make('material.sku')
                ->label('Артикул'),
            TextEntry::make('material.category')
                ->label('Категория')
                ->formatStateUsing(fn (?string $state): string => static::categoryOptions()[$state] ?? ($state ?? '—')),
            TextEntry::make('material.unit')
                ->label('Ед. изм.')
                ->formatStateUsing(fn (?string $state): string => static::unitOptions()[$state] ?? ($state ?? '—')),
            TextEntry::make('quantity_on_hand')
                ->label('Остаток'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material.name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('material.sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('material.category')
                    ->label('Категория')
                    ->formatStateUsing(fn (?string $state): string => static::categoryOptions()[$state] ?? ($state ?? '—'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('material.unit')
                    ->label('Ед. изм.')
                    ->formatStateUsing(fn (?string $state): string => static::unitOptions()[$state] ?? ($state ?? '—')),
                TextColumn::make('quantity_on_hand')
                    ->label('Остаток')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
                ...static::stockMutationActions(),
            ]);
    }

    /** @return list<Action> */
    public static function stockMutationActions(): array
    {
        return [
            Action::make('receive')
                ->label('Приход')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->form([
                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->required()
                        ->gt(0),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (StockItemModel $record, array $data): void {
                    try {
                        $movementId = app(SequentialEntityIdGenerator::class)->next('warehouse_movement')->value;
                        app(ReceiveMaterialHandler::class)->handle(new ReceiveMaterialCommand(
                            (int) $record->id,
                            $movementId,
                            (string) $data['quantity'],
                            $data['comment'] ?? null,
                        ));
                        Notification::make()->title('Приход выполнен')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('writeOff')
                ->label('Списание')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('danger')
                ->form([
                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->required()
                        ->gt(0),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (StockItemModel $record, array $data): void {
                    try {
                        app(WriteOffMaterialHandler::class)->handle(new WriteOffMaterialCommand(
                            (int) $record->id,
                            (string) $data['quantity'],
                            $data['comment'] ?? null,
                        ));
                        Notification::make()->title('Списание выполнено')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('changeStock')
                ->label('Корректировка')
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->form([
                    TextInput::make('quantity')
                        ->label('Новый остаток')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (StockItemModel $record, array $data): void {
                    try {
                        $movementId = app(SequentialEntityIdGenerator::class)->next('warehouse_movement')->value;
                        app(ChangeStockHandler::class)->handle(new ChangeStockCommand(
                            (int) $record->id,
                            $movementId,
                            (string) $data['quantity'],
                            $data['comment'] ?? null,
                        ));
                        Notification::make()->title('Остаток обновлён')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public static function getHeaderActions(): array
    {
        return [
            Action::make('openStockItem')
                ->label('Добавить позицию')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),
                    Select::make('category')
                        ->label('Категория')
                        ->options(static::categoryOptions())
                        ->required(),
                    Select::make('unit')
                        ->label('Ед. изм.')
                        ->options(static::unitOptions())
                        ->required()
                        ->default(UnitOfMeasure::Piece->value)
                        ->searchable(),
                    TextInput::make('initialQuantity')
                        ->label('Начальный остаток')
                        ->numeric()
                        ->required()
                        ->default('0')
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    try {
                        $ids = app(SequentialEntityIdGenerator::class);
                        app(OpenStockItemHandler::class)->handle(new OpenStockItemCommand(
                            $ids->next('stock_item')->value,
                            $ids->next('material')->value,
                            $data['name'],
                            $data['unit'],
                            $data['category'],
                            (string) $data['initialQuantity'],
                        ));
                        Notification::make()->title('Позиция добавлена')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockItems::route('/'),
            'view' => ViewStockItem::route('/{record}'),
        ];
    }
}
