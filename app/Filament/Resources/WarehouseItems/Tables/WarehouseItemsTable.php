<?php

namespace App\Filament\Resources\WarehouseItems\Tables;

use App\Domain\Warehouse\Enum\StockMovementType;
use App\Infrastructure\Warehouse\Persistence\Eloquent\StockMovementModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('quantity')->label('Остаток'),
                TextColumn::make('unit')->label('Ед.'),
                TextColumn::make('price')->label('Цена')->money('RUB'),
            ])
            ->recordActions([
                EditAction::make(),
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
                    ->action(function (WarehouseItemModel $record, array $data): void {
                        $quantity = number_format((float) $data['quantity'], 3, '.', '');

                        DB::transaction(function () use ($record, $quantity, $data): void {
                            $record->increment('quantity', $quantity);

                            StockMovementModel::query()->create([
                                'warehouse_item_id' => $record->id,
                                'type' => StockMovementType::Received,
                                'quantity' => $quantity,
                                'comment' => $data['comment'] ?? null,
                                'user_id' => Auth::id(),
                            ]);
                        });

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
                    ->action(function (WarehouseItemModel $record, array $data): void {
                        $quantity = number_format((float) $data['quantity'], 3, '.', '');

                        if (bccomp((string) $record->quantity, $quantity, 3) < 0) {
                            Notification::make()
                                ->danger()
                                ->title('Недостаточно остатка на складе')
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($record, $quantity, $data): void {
                            $record->decrement('quantity', $quantity);

                            StockMovementModel::query()->create([
                                'warehouse_item_id' => $record->id,
                                'type' => StockMovementType::WrittenOff,
                                'quantity' => $quantity,
                                'comment' => $data['comment'] ?? null,
                                'user_id' => Auth::id(),
                            ]);
                        });

                        Notification::make()->success()->title('Списание оформлено')->send();
                    }),
            ]);
    }
}
