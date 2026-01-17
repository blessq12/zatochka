<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderWork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderWorksRelationManager extends RelationManager
{
    protected static string $relationship = 'orderWorks';

    protected static ?string $title = 'Работы';

    protected static ?string $modelLabel = 'Работа';

    protected static ?string $pluralModelLabel = 'Работы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('work_type')
                    ->label('Тип работы')
                    ->options(OrderWork::getAvailableWorkTypes())
                    ->default(OrderWork::WORK_TYPE_REPAIR)
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('work_price')
                    ->label('Стоимость работы')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->default(0),

                Forms\Components\TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->default(1)
                    ->visible(fn($get) => $get('work_type') === OrderWork::WORK_TYPE_SHARPENING),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Цена за единицу')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->visible(fn($get) => $get('work_type') === OrderWork::WORK_TYPE_SHARPENING),

                Forms\Components\TextInput::make('work_time_minutes')
                    ->label('Время работы (минуты)')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('work_type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderWork::WORK_TYPE_SHARPENING => 'success',
                        OrderWork::WORK_TYPE_REPAIR => 'primary',
                        OrderWork::WORK_TYPE_DIAGNOSTIC => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => OrderWork::getAvailableWorkTypes()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('work_price')
                    ->label('Стоимость работы')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('materials_cost')
                    ->label('Стоимость материалов')
                    ->money('RUB')
                    ->default(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('materials_count')
                    ->label('Материалов')
                    ->counts('materials')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('work_time_minutes')
                    ->label('Время (мин)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('work_type')
                    ->label('Тип работы')
                    ->options(OrderWork::getAvailableWorkTypes()),

                Tables\Filters\Filter::make('is_deleted')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('is_deleted', false));
    }
}
