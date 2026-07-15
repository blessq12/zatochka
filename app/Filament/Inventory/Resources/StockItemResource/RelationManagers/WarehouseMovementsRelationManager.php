<?php

namespace App\Filament\Inventory\Resources\StockItemResource\RelationManagers;

use App\Domain\Inventory\VO\MovementType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WarehouseMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Движения';

    protected static ?string $recordTitleAttribute = 'id';

    public function isReadOnly(): bool
    {
        return true;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Движения по складу')
            ->emptyStateHeading('Движений пока нет')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Дата')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        MovementType::Receipt->value => 'Приход',
                        MovementType::WriteOff->value => 'Списание',
                        MovementType::Adjustment->value => 'Корректировка',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        MovementType::Receipt->value => 'success',
                        MovementType::WriteOff->value => 'danger',
                        MovementType::Adjustment->value => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
