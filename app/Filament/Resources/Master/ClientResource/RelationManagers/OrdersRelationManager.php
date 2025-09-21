<?php

namespace App\Filament\Resources\Master\ClientResource\RelationManagers;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderUrgency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы клиента';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->disabled(),

                        Forms\Components\TextInput::make('client.full_name')
                            ->label('Клиент')
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label('Тип заказа')
                            ->options([
                                'sharpening' => 'Заточка',
                                'repair' => 'Ремонт',
                                'delivery' => 'Доставка',
                            ])
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Статус заказа')
                            ->options(OrderStatus::getOptions())
                            ->disabled(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                'low' => 'Низкая',
                                'normal' => 'Обычная',
                                'high' => 'Высокая',
                                'urgent' => 'Срочная',
                            ])
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Описание заказа')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->disabled()
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('final_price')
                            ->label('Итоговая цена')
                            ->disabled()
                            ->prefix('₽'),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Оплачен')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Дата оплаты')
                            ->disabled()
                            ->displayFormat('d.m.Y H:i'),
                    ])->columns(3),

                Forms\Components\Section::make('Временные рамки')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Создан')
                            ->disabled()
                            ->displayFormat('d.m.Y H:i'),

                        Forms\Components\DateTimePicker::make('estimated_completion')
                            ->label('Планируемое завершение')
                            ->disabled()
                            ->displayFormat('d.m.Y H:i'),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Завершен')
                            ->disabled()
                            ->displayFormat('d.m.Y H:i'),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип услуги')
                    ->formatStateUsing(function ($state): string {
                        if (is_object($state)) {
                            if (method_exists($state, 'getLabel')) {
                                return $state->getLabel();
                            } elseif (method_exists($state, 'value')) {
                                $state = $state->value;
                            }
                        }
                        return match ($state) {
                            'sharpening' => 'Заточка',
                            'repair' => 'Ремонт',
                            'delivery' => 'Доставка',
                            default => (string) $state,
                        };
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(function ($state): string {
                        if (is_object($state)) {
                            if (method_exists($state, 'getLabel')) {
                                return $state->getLabel();
                            } elseif (method_exists($state, 'value')) {
                                $state = $state->value;
                            }
                        }
                        return is_string($state) ? OrderStatus::from($state)->getLabel() : $state->getLabel();
                    })
                    ->badge()
                    ->color(function ($state): string {
                        if (is_object($state)) {
                            $state = $state->value ?? $state;
                        }
                        return match ($state) {
                            OrderStatus::NEW->value => 'gray',
                            OrderStatus::CONSULTATION->value => 'blue',
                            OrderStatus::DIAGNOSTIC->value => 'yellow',
                            OrderStatus::IN_WORK->value => 'warning',
                            OrderStatus::WAITING_PARTS->value => 'orange',
                            OrderStatus::READY->value => 'success',
                            OrderStatus::ISSUED->value => 'info',
                            OrderStatus::CANCELLED->value => 'danger',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(function ($state): string {
                        if (is_object($state)) {
                            if (method_exists($state, 'getLabel')) {
                                return $state->getLabel();
                            } elseif (method_exists($state, 'value')) {
                                $state = $state->value;
                            }
                        }
                        return match ($state) {
                            'low' => 'Низкая',
                            'normal' => 'Обычная',
                            'high' => 'Высокая',
                            'urgent' => 'Срочная',
                            default => (string) $state,
                        };
                    })
                    ->badge()
                    ->color(function ($state): string {
                        if (is_object($state)) {
                            $state = $state->value ?? $state;
                        }
                        return match ($state) {
                            'low' => 'gray',
                            'normal' => 'blue',
                            'high' => 'orange',
                            'urgent' => 'red',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Оплачен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::getOptions()),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип заказа')
                    ->options([
                        'sharpening' => 'Заточка',
                        'repair' => 'Ремонт',
                        'delivery' => 'Доставка',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Просроченные')
                    ->query(fn(Builder $query): Builder => $query->where('estimated_completion', '<', now())
                        ->where('status', '!=', OrderStatus::ISSUED->value)),
            ])
            ->headerActions([
                // Нет действий для создания в read-only режиме
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Нет bulk actions для read-only режима
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Номер заказа')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('client.full_name')
                            ->label('Клиент'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('Тип услуги')
                            ->formatStateUsing(function ($state): string {
                                if (is_object($state)) {
                                    if (method_exists($state, 'getLabel')) {
                                        return $state->getLabel();
                                    } elseif (method_exists($state, 'value')) {
                                        $state = $state->value;
                                    }
                                }
                                return match ($state) {
                                    'sharpening' => 'Заточка',
                                    'repair' => 'Ремонт',
                                    'delivery' => 'Доставка',
                                    default => (string) $state,
                                };
                            })
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Статус')
                            ->formatStateUsing(function ($state): string {
                                if (is_object($state)) {
                                    if (method_exists($state, 'getLabel')) {
                                        return $state->getLabel();
                                    } elseif (method_exists($state, 'value')) {
                                        $state = $state->value;
                                    }
                                }
                                return is_string($state) ? OrderStatus::from($state)->getLabel() : $state->getLabel();
                            })
                            ->badge()
                            ->color(function ($state): string {
                                if (is_object($state)) {
                                    $state = $state->value ?? $state;
                                }
                                return match ($state) {
                                    OrderStatus::NEW->value => 'gray',
                                    OrderStatus::CONSULTATION->value => 'blue',
                                    OrderStatus::DIAGNOSTIC->value => 'yellow',
                                    OrderStatus::IN_WORK->value => 'warning',
                                    OrderStatus::WAITING_PARTS->value => 'orange',
                                    OrderStatus::READY->value => 'success',
                                    OrderStatus::ISSUED->value => 'success',
                                    OrderStatus::CANCELLED->value => 'danger',
                                    default => 'gray',
                                };
                            }),

                        Infolists\Components\TextEntry::make('urgency')
                            ->label('Срочность')
                            ->formatStateUsing(function ($state): string {
                                if (is_object($state)) {
                                    if (method_exists($state, 'getLabel')) {
                                        return $state->getLabel();
                                    } elseif (method_exists($state, 'value')) {
                                        $state = $state->value;
                                    }
                                }
                                return match ($state) {
                                    'low' => 'Низкая',
                                    'normal' => 'Обычная',
                                    'high' => 'Высокая',
                                    'urgent' => 'Срочная',
                                    default => (string) $state,
                                };
                            })
                            ->badge()
                            ->color(function ($state): string {
                                if (is_object($state)) {
                                    $state = $state->value ?? $state;
                                }
                                return match ($state) {
                                    'low' => 'gray',
                                    'normal' => 'blue',
                                    'high' => 'orange',
                                    'urgent' => 'red',
                                    default => 'gray',
                                };
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Описание заказа')
                    ->schema([
                        Infolists\Components\TextEntry::make('problem_description')
                            ->label('Описание проблемы')
                            ->placeholder('Не указано')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->placeholder('Нет заметок')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Финансы')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Общая сумма')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('final_price')
                            ->label('Итоговая цена')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('cost_price')
                            ->label('Себестоимость')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('profit')
                            ->label('Прибыль')
                            ->money('RUB')
                            ->placeholder('Не рассчитана')
                            ->color(fn($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),

                        Infolists\Components\IconEntry::make('is_paid')
                            ->label('Оплачен')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Дата оплаты')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Не оплачен')
                            ->visible(fn($record) => $record->is_paid),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Фотографии')
                    ->schema([
                        Infolists\Components\TextEntry::make('before_photos_info')
                            ->label('Фото "До" (что принес клиент)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('before_photos');
                                if ($photos->count() === 0) {
                                    return 'Фотографии не загружены';
                                }

                                $html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                                foreach ($photos as $photo) {
                                    $html .= '<div class="relative">';
                                    $html .= '<img src="' . $photo->getUrl() . '" alt="Фото до" class="w-full h-32 object-cover rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer" onclick="window.open(this.src, \'_blank\')">';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn($record) => $record->getMedia('before_photos')->count() > 0),

                        Infolists\Components\TextEntry::make('after_photos_info')
                            ->label('Фото "После" (результат работ)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('after_photos');
                                if ($photos->count() === 0) {
                                    return 'Фотографии не загружены';
                                }

                                $html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                                foreach ($photos as $photo) {
                                    $html .= '<div class="relative">';
                                    $html .= '<img src="' . $photo->getUrl() . '" alt="Фото после" class="w-full h-32 object-cover rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer" onclick="window.open(this.src, \'_blank\')">';
                                    $html .= '<div class="absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">' . $photo->file_name . '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn($record) => $record->getMedia('after_photos')->count() > 0),

                        Infolists\Components\TextEntry::make('no_photos')
                            ->label('')
                            ->state('Фотографии не загружены')
                            ->visible(fn($record) => $record->getMedia('before_photos')->count() === 0 && $record->getMedia('after_photos')->count() === 0)
                            ->color('gray'),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Временные метки')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('estimated_completion')
                            ->label('Планируемое завершение')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Не указано'),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Завершен')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Не завершен'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
