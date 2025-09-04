<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\WarehouseResource\Pages;
use App\Models\Warehouse as WarehouseModel;
use App\Models\Branch;
use App\Domain\Inventory\ValueObjects\WarehouseName;
use App\Domain\Inventory\Services\WarehouseService;
use App\Domain\Inventory\Interfaces\WarehouseRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WarehouseResource extends Resource
{
    protected static ?string $model = WarehouseModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Склад';

    protected static ?string $navigationLabel = 'Склады';

    protected static ?string $modelLabel = 'Склад';

    protected static ?string $pluralModelLabel = 'Склады';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название склада')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Главный склад'),

                        Select::make('branch_id')
                            ->label('Филиал')
                            ->options(Branch::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Выберите филиал')
                            ->helperText('Один филиал может иметь только один склад'),

                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->placeholder('Дополнительная информация о складе'),
                    ])
                    ->columns(2),

                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Активные склады доступны для операций'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'Активен',
                        'danger' => 'Неактивен',
                    ])
                    ->getStateUsing(
                        fn(Model $record): string =>
                        $record->is_active ? 'Активен' : 'Неактивен'
                    ),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->options(Branch::pluck('name', 'id')),

                TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все склады')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (array $data, Model $record): Model {
                        $warehouseService = app(WarehouseService::class);

                        $warehouseId = (int) $record->id;
                        $name = WarehouseName::fromString($data['name']);
                        $branchId = $data['branch_id'] ? (int) $data['branch_id'] : null;

                        if ($data['is_active'] && !$record->is_active) {
                            $warehouseService->activateWarehouse($warehouseId);
                        } elseif (!$data['is_active'] && $record->is_active) {
                            $warehouseService->deactivateWarehouse($warehouseId);
                        }

                        if ($data['name'] !== $record->name) {
                            $warehouseService->updateWarehouseName($warehouseId, $name);
                        }

                        if ($data['description'] !== $record->description) {
                            $warehouseService->updateWarehouseDescription($warehouseId, $data['description']);
                        }

                        if ($data['branch_id'] !== $record->branch_id) {
                            if ($data['branch_id']) {
                                $warehouseService->assignWarehouseToBranch($warehouseId, $branchId);
                            } else {
                                $warehouseService->unassignWarehouseFromBranch($warehouseId);
                            }
                        }

                        Notification::make()
                            ->title('Склад обновлен')
                            ->success()
                            ->send();

                        return $record->fresh();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record): void {
                        $warehouseService = app(WarehouseService::class);
                        $warehouseId = (int) $record->id;

                        $warehouseService->deleteWarehouse($warehouseId);

                        Notification::make()
                            ->title('Склад удален')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function (array $records): void {
                            $warehouseService = app(WarehouseService::class);

                            foreach ($records as $record) {
                                $warehouseId = (int) $record->id;
                                $warehouseService->deleteWarehouse($warehouseId);
                            }

                            Notification::make()
                                ->title('Склады удалены')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('branch')
            ->where('is_deleted', false);
    }
}
