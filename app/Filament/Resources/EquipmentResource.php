<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Оборудование';

    protected static ?string $modelLabel = 'Оборудование';

    protected static ?string $pluralModelLabel = 'Оборудование';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Владелец (клиент)')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Клиент, которому принадлежит оборудование'),

                        Forms\Components\TextInput::make('name')
                            ->label('Название оборудования')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Дрель Bosch GSB 16 RE'),

                        Forms\Components\TextInput::make('type')
                            ->label('Тип оборудования')
                            ->maxLength(255)
                            ->placeholder('Например: Дрель, Шуруповерт, Болгарка'),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Серийный номер')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->helperText('Уникальный серийный номер оборудования'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Производитель')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->label('Производитель/Бренд')
                            ->maxLength(255)
                            ->placeholder('Например: Bosch, Makita, DeWalt')
                            ->helperText('Бренд или производитель оборудования'),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель')
                            ->maxLength(255)
                            ->placeholder('Например: GSB 16 RE'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалено')
                            ->default(false)
                            ->helperText('Пометить оборудование как удаленное'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Производитель')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Модель')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Серийный номер')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Владелец')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Обращений')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('last_order_date')
                    ->label('Последнее обращение')
                    ->getStateUsing(function ($record) {
                        $lastOrder = $record->orders()->latest()->first();
                        return $lastOrder ? $lastOrder->created_at->format('d.m.Y') : '—';
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалено')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Владелец')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все оборудование')
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'view' => Pages\ViewEquipment::route('/{record}'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
