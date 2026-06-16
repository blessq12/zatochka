<?php

namespace App\Filament\Resources\WarehouseItems\Tables;

use App\Application\Warehouse\Command\ReceiveStockCommand;
use App\Application\Warehouse\Command\WriteOffStockCommand;
use App\Application\Warehouse\CommandHandler\ReceiveStockHandler;
use App\Application\Warehouse\CommandHandler\WriteOffStockHandler;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WarehouseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('sku')->label('Артикул'),
                TextColumn::make('name')->label('Название'),
                TextColumn::make('category_name')->label('Категория')->placeholder('—'),
                TextColumn::make('quantity')->label('Остаток'),
                TextColumn::make('unit')->label('Ед.'),
                TextColumn::make('price')->label('Цена')->money('RUB'),
            ])
            ->recordActions([
                Action::make('receive')
                    ->label('Приход')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(0.001)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий')
                            ->maxLength(500),
                    ])
                    ->action(function (WarehouseItemModel $record, array $data, ReceiveStockHandler $handler): void {
                        $handler->handle(new ReceiveStockCommand(
                            warehouseItemId: $record->id,
                            quantity: number_format((float) $data['quantity'], 3, '.', ''),
                            comment: $data['comment'] ?? null,
                            userId: Auth::id(),
                        ));

                        Notification::make()->success()->title('Приход оформлен')->send();
                    }),
                Action::make('writeOff')
                    ->label('Списание')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(0.001)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий')
                            ->maxLength(500),
                    ])
                    ->action(function (WarehouseItemModel $record, array $data, WriteOffStockHandler $handler): void {
                        $handler->handle(new WriteOffStockCommand(
                            warehouseItemId: $record->id,
                            quantity: number_format((float) $data['quantity'], 3, '.', ''),
                            comment: $data['comment'] ?? null,
                            userId: Auth::id(),
                        ));

                        Notification::make()->success()->title('Списание оформлено')->send();
                    }),
            ]);
    }
}
