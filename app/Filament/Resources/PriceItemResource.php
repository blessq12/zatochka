<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceItemResource\Pages;
use App\Filament\Resources\PriceItemResource\RelationManagers;
use App\Models\PriceItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceItemResource extends Resource
{
    protected static ?string $model = PriceItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Прайс-листы';

    protected static ?string $modelLabel = 'Позиция прайса';

    protected static ?string $pluralModelLabel = 'Прайс-листы';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->label('Тип услуги')
                            ->options([
                                PriceItem::TYPE_SHARPENING => 'Заточка',
                                PriceItem::TYPE_REPAIR => 'Ремонт',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('category_title')
                            ->label('Название категории')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: ИНСТРУМЕНТЫ ДЛЯ МАНИКЮРА / ПЕДИКЮРА'),

                        Forms\Components\TextInput::make('name')
                            ->label('Название услуги')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Ножницы/кусачки'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Дополнительное описание услуги')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Например: 350, 800/400, 1700')
                            ->helperText('Можно указать диапазон через "/"'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->helperText('Чем меньше число, тем раньше в списке'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->helperText('Отключенные позиции не отображаются на сайте'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Тип услуги')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        PriceItem::TYPE_SHARPENING => 'success',
                        PriceItem::TYPE_REPAIR => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        PriceItem::TYPE_SHARPENING => 'Заточка',
                        PriceItem::TYPE_REPAIR => 'Ремонт',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('category_title')
                    ->label('Категория')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(PriceItem $record): ?string => $record->category_title)
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(PriceItem $record): ?string => $record->name)
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn(string $state): string => $state . '₽')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Сортировка')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Тип услуги')
                    ->options([
                        PriceItem::TYPE_SHARPENING => 'Заточка',
                        PriceItem::TYPE_REPAIR => 'Ремонт',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->native(false),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Удалить'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPriceItems::route('/'),
            'create' => Pages\CreatePriceItem::route('/create'),
            'edit' => Pages\EditPriceItem::route('/{record}/edit'),
        ];
    }
}
