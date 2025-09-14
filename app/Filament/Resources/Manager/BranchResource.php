<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BranchResource\Pages;
use App\Filament\Resources\Manager\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Компания';
    protected static ?string $pluralLabel = 'Филиалы';
    protected static ?string $modelLabel = 'Филиал';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Компания')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('name')
                            ->label('Название филиала')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Код филиала')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('address')
                            ->label('Адрес')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Контакты')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Рабочее время')
                    ->schema([
                        Forms\Components\TextInput::make('working_hours')
                            ->label('Рабочие часы (текст)')
                            ->placeholder('Пн-Пт: 10:00-19:00, Сб: 11:00-16:00')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('opening_time')
                            ->label('Время открытия')
                            ->placeholder('10:00')
                            ->maxLength(5),
                        Forms\Components\TextInput::make('closing_time')
                            ->label('Время закрытия')
                            ->placeholder('19:00')
                            ->maxLength(5),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Section::make('Понедельник')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.monday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.monday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.monday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Вторник')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.tuesday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.tuesday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.tuesday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Среда')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.wednesday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.wednesday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.wednesday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Четверг')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.thursday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.thursday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.thursday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Пятница')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.friday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.friday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.friday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Суббота')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.saturday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.saturday.start')
                                            ->label('Начало')
                                            ->placeholder('11:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.saturday.end')
                                            ->label('Конец')
                                            ->placeholder('16:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),

                                Forms\Components\Section::make('Воскресенье')
                                    ->schema([
                                        Forms\Components\Toggle::make('working_schedule.sunday.is_working')
                                            ->label('Рабочий день'),
                                        Forms\Components\TextInput::make('working_schedule.sunday.start')
                                            ->label('Начало')
                                            ->placeholder('10:00')
                                            ->maxLength(5),
                                        Forms\Components\TextInput::make('working_schedule.sunday.end')
                                            ->label('Конец')
                                            ->placeholder('19:00')
                                            ->maxLength(5),
                                    ])
                                    ->columns(1)
                                    ->compact(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Координаты')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Широта')
                            ->numeric()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Долгота')
                            ->numeric()
                            ->step(0.0000001),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                        Forms\Components\Toggle::make('is_main')
                            ->label('Главный филиал'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('working_hours')
                    ->label('Рабочие часы')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_main')
                    ->label('Главный')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
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
                    ->label('Тип')
                    ->placeholder('Все филиалы')
                    ->trueLabel('Только главные')
                    ->falseLabel('Только обычные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
