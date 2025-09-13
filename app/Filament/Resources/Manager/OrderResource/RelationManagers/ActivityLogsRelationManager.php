<?php

namespace App\Filament\Resources\Manager\OrderResource\RelationManagers;

use App\Domain\Order\Service\FieldValueFormatterService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'История изменений';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Пользователь')
                    ->placeholder('Система'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Действие'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Событие')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('changes')
                    ->label('Изменения')
                    ->formatStateUsing(function ($record) {
                        $properties = $record->properties ?? [];

                        if (empty($properties) || $record->event === 'created') {
                            return '—';
                        }

                        $changes = [];

                        // Для updated событий показываем изменения
                        if ($record->event === 'updated' && isset($properties['attributes']) && isset($properties['old'])) {
                            foreach ($properties['attributes'] as $field => $newValue) {
                                $oldValue = $properties['old'][$field] ?? null;

                                if ($oldValue !== $newValue) {
                                    $fieldLabel = FieldValueFormatterService::formatFieldLabel($field);
                                    $oldDisplay = FieldValueFormatterService::formatValue($field, $oldValue);
                                    $newDisplay = FieldValueFormatterService::formatValue($field, $newValue);
                                    $changes[] = "{$fieldLabel} => {$oldDisplay} / {$newDisplay}";
                                }
                            }
                        }

                        return empty($changes) ? '—' : implode(', ', $changes);
                    })
                    ->wrap()
                    ->limit(100),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Создан',
                        'updated' => 'Обновлен',
                        'deleted' => 'Удален',
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('Нет записей в истории');
    }
}
