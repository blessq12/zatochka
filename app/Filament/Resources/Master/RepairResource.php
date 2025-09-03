<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\RepairResource\Pages;
use App\Models\Repair;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepairResource extends Resource
{
    protected static ?string $model = Repair::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Мои работы';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->options(Order::where('master_id', fn () => auth()->id())->pluck('order_number', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('order.client.full_name')
                            ->label('Клиент')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('order.serviceType.name')
                            ->label('Тип услуги')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Выполненная работа')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание работы')
                            ->rows(4)
                            ->required()
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('work_time_minutes')
                            ->label('Время работы (минуты)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Стоимость работы')
                            ->numeric()
                            ->prefix('₽')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Использованные материалы')
                    ->schema([
                        Forms\Components\KeyValue::make('used_materials')
                            ->label('Материалы')
                            ->keyLabel('Материал')
                            ->valueLabel('Количество')
                            ->addActionLabel('Добавить материал'),
                    ])->collapsible(),

                Forms\Components\Section::make('Фотографии')
                    ->schema([
                        Forms\Components\FileUpload::make('before_photos')
                            ->label('Фото до работы')
                            ->multiple()
                            ->image()
                            ->maxFiles(5),

                        Forms\Components\FileUpload::make('after_photos')
                            ->label('Фото после работы')
                            ->multiple()
                            ->image()
                            ->maxFiles(5),

                        Forms\Components\FileUpload::make('work_photos')
                            ->label('Фото процесса работы')
                            ->multiple()
                            ->image()
                            ->maxFiles(10),
                    ])->columns(3),

                Forms\Components\Section::make('Документы')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Документы')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxFiles(5),
                    ])->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.serviceType.name')
                    ->label('Услуга')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание работы')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('work_time_minutes')
                    ->label('Время работы')
                    ->formatStateUsing(fn (int $state): string => gmdate('H:i', $state * 60))
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('before_photos_count')
                    ->label('Фото до')
                    ->counts('before_photos')
                    ->sortable(),

                Tables\Columns\TextColumn::make('after_photos_count')
                    ->label('Фото после')
                    ->counts('after_photos')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn (Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Заказ')
                    ->options(Order::where('master_id', fn () => auth()->id())->pluck('order_number', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepairs::route('/'),
            'create' => Pages\CreateRepair::route('/create'),
            'view' => Pages\ViewRepair::route('/{record}'),
            'edit' => Pages\EditRepair::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('order', function (Builder $query) {
                $query->where('master_id', fn () => auth()->id());
            })
            ->with(['order.client', 'order.serviceType']);
    }
}
