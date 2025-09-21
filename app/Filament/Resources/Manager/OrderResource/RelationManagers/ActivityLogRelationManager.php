<?php

namespace App\Filament\Resources\Manager\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'История изменений';

    protected static ?string $modelLabel = 'Изменение';

    protected static ?string $pluralModelLabel = 'История изменений';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Описание')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Событие')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'created' => 'Создан',
                        'updated' => 'Обновлен',
                        'deleted' => 'Удален',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Система'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата и время')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->created_at->format('d.m.Y H:i:s')),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Изменения')
                    ->getStateUsing(function (Activity $record) {
                        if (!$record->properties || empty($record->properties->get('attributes'))) {
                            return '—';
                        }

                        $attributes = $record->properties->get('attributes', []);
                        $old = $record->properties->get('old', []);

                        $changes = [];
                        foreach ($attributes as $key => $newValue) {
                            $oldValue = $old[$key] ?? null;

                            if ($oldValue !== $newValue) {
                                $fieldLabels = [
                                    'client_id' => 'Клиент',
                                    'branch_id' => 'Филиал',
                                    'manager_id' => 'Менеджер',
                                    'type' => 'Тип',
                                    'status' => 'Статус',
                                    'urgency' => 'Срочность',
                                    'estimated_price' => 'Предварительная цена',
                                    'actual_price' => 'Фактическая цена',
                                    'internal_notes' => 'Внутренние заметки',
                                    'problem_description' => 'Описание проблемы',
                                ];

                                $label = $fieldLabels[$key] ?? $key;
                                $formattedOld = $this->formatValue($key, $oldValue);
                                $formattedNew = $this->formatValue($key, $newValue);

                                $changes[] = "{$label}: {$formattedOld} → {$formattedNew}";
                            }
                        }

                        return empty($changes) ? '—' : implode('; ', $changes);
                    })
                    ->html()
                    ->wrap()
                    ->limit(200),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Событие')
                    ->options([
                        'created' => 'Создан',
                        'updated' => 'Обновлен',
                        'deleted' => 'Удален',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('По дату'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Нет действий для логов - только просмотр в таблице
            ])
            ->bulkActions([
                // Нет массовых действий для логов
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Автообновление каждые 30 секунд
    }

    private function formatValue(string $field, $value): string
    {
        if ($value === null) {
            return '<em>не указано</em>';
        }

        switch ($field) {
            case 'client_id':
                $client = \App\Models\Client::find($value);
                return $client ? (string) $client->name : "ID: {$value}";

            case 'branch_id':
                $branch = \App\Models\Branch::find($value);
                return $branch ? (string) $branch->name : "ID: {$value}";

            case 'manager_id':
                $manager = \App\Models\User::find($value);
                return $manager ? (string) $manager->name : "ID: {$value}";

            case 'status':
                $statuses = \App\Models\Order::getAvailableStatuses();
                return $statuses[$value] ?? (string) $value;

            case 'type':
                $types = \App\Models\Order::getAvailableTypes();
                return $types[$value] ?? (string) $value;

            case 'urgency':
                $urgencies = \App\Models\Order::getAvailableUrgencies();
                return $urgencies[$value] ?? (string) $value;

            case 'estimated_price':
            case 'actual_price':
                return number_format((float) $value, 2, ',', ' ') . ' ₽';

            default:
                if (is_string($value) && strlen($value) > 50) {
                    return substr($value, 0, 50) . '...';
                }
                return (string) $value;
        }
    }
}
