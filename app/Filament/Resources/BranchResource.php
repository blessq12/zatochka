<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\MapPicker;
use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Филиалы';

    protected static ?string $modelLabel = 'Филиал';

    protected static ?string $pluralModelLabel = 'Филиалы';

    protected static ?string $navigationGroup = 'Организация';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Компания')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Название филиала'),
                        Forms\Components\TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Уникальный код филиала'),
                        Forms\Components\Textarea::make('address')
                            ->label('Адрес')
                            ->required()
                            ->rows(2)
                            ->placeholder('Полный адрес филиала')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->placeholder('Дополнительная информация о филиале')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Контакты')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('+7 (999) 123-45-67')
                            ->helperText('Можно ввести телефон в любом формате'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('email@example.com'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Расписание работы')
                    ->schema([
                        Forms\Components\TextInput::make('opening_time')
                            ->label('Время открытия')
                            ->maxLength(255)
                            ->placeholder('09:00')
                            ->helperText('Общее время открытия филиала'),
                        Forms\Components\TextInput::make('closing_time')
                            ->label('Время закрытия')
                            ->maxLength(255)
                            ->placeholder('18:00')
                            ->helperText('Общее время закрытия филиала'),
                        Forms\Components\Textarea::make('working_hours')
                            ->label('Рабочие часы (текст)')
                            ->rows(2)
                            ->placeholder('Пн-Пт: 9:00-18:00, Сб-Вс: выходной')
                            ->helperText('Текстовое описание рабочих часов (для отображения на сайте)')
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('working_schedule')
                            ->label('График работы по дням недели')
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->label('День недели')
                                    ->options([
                                        'monday' => 'Понедельник',
                                        'tuesday' => 'Вторник',
                                        'wednesday' => 'Среда',
                                        'thursday' => 'Четверг',
                                        'friday' => 'Пятница',
                                        'saturday' => 'Суббота',
                                        'sunday' => 'Воскресенье',
                                    ])
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_working')
                                    ->label('Рабочий день')
                                    ->default(true)
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TimePicker::make('start')
                                    ->label('Время начала')
                                    ->required()
                                    ->visible(fn($get) => $get('is_working'))
                                    ->columnSpan(1),
                                Forms\Components\TimePicker::make('end')
                                    ->label('Время окончания')
                                    ->required()
                                    ->visible(fn($get) => $get('is_working'))
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('note')
                                    ->label('Примечание')
                                    ->maxLength(255)
                                    ->placeholder('Рабочий день / Выходной')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->defaultItems(7)
                            ->itemLabel(fn(array $state): ?string => match ($state['day'] ?? null) {
                                'monday' => 'Понедельник',
                                'tuesday' => 'Вторник',
                                'wednesday' => 'Среда',
                                'thursday' => 'Четверг',
                                'friday' => 'Пятница',
                                'saturday' => 'Суббота',
                                'sunday' => 'Воскресенье',
                                default => null,
                            })
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    // Преобразуем массив в структуру для Repeater
                                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    $result = [];
                                    foreach ($days as $day) {
                                        if (isset($state[$day])) {
                                            $result[] = array_merge(['day' => $day], $state[$day]);
                                        } else {
                                            $result[] = [
                                                'day' => $day,
                                                'is_working' => false,
                                                'start' => null,
                                                'end' => null,
                                                'note' => 'Выходной',
                                            ];
                                        }
                                    }
                                    return $result;
                                }

                                // Если нет данных, создаем дефолтные значения для всех дней
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                $result = [];
                                foreach ($days as $day) {
                                    $result[] = [
                                        'day' => $day,
                                        'is_working' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                                        'start' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? '09:00' : null,
                                        'end' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? '18:00' : null,
                                        'note' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'Рабочий день' : 'Выходной',
                                    ];
                                }
                                return $result;
                            })
                            ->dehydrateStateUsing(function ($state) {
                                if (!is_array($state)) {
                                    return [];
                                }

                                // Преобразуем обратно в структуру по дням
                                $result = [];
                                foreach ($state as $item) {
                                    if (isset($item['day'])) {
                                        $day = $item['day'];
                                        unset($item['day']);
                                        $result[$day] = $item;
                                    }
                                }
                                return $result;
                            })
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Геолокация')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Широта')
                            ->numeric()
                            ->step(0.000001)
                            ->hiddenLabel()
                            ->visible(false)
                            ->dehydrated(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Долгота')
                            ->numeric()
                            ->step(0.000001)
                            ->hiddenLabel()
                            ->visible(false)
                            ->dehydrated(),
                        MapPicker::make('location')
                            ->label('Выберите местоположение на карте')
                            ->latitude('latitude')
                            ->longitude('longitude')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Статус и настройки')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->required(),
                        Forms\Components\Toggle::make('is_main')
                            ->label('Главный филиал')
                            ->default(false)
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false)
                            ->required(),
                    ])
                    ->columns(4)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Компания')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Код')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->address)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('opening_time')
                    ->label('Открытие')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('closing_time')
                    ->label('Закрытие')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_main')
                    ->label('Главный')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Сортировка')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удален')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Компания')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все филиалы')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                Tables\Filters\TernaryFilter::make('is_main')
                    ->label('Главный филиал')
                    ->placeholder('Все филиалы')
                    ->trueLabel('Только главные')
                    ->falseLabel('Обычные'),
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все филиалы')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'view' => Pages\ViewBranch::route('/{record}'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
