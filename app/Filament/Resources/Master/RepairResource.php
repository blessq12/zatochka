<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\RepairResource\Pages;
use App\Filament\Resources\Master\RepairResource\RelationManagers;
use App\Models\Repair;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Builder;

class RepairResource extends Resource
{
    protected static ?string $model = Repair::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Ремонты';
    protected static ?string $pluralLabel = 'Ремонты';
    protected static ?string $modelLabel = 'Ремонт';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number', function ($query) {
                                // Исключаем заказы, для которых уже создан ремонт
                                return $query->whereDoesntHave('repairs');
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->order_number)
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание работ')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Время и стоимость')
                    ->schema([
                        Forms\Components\TextInput::make('work_time_minutes')
                            ->label('Время работы (минуты)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('мин'),

                        Forms\Components\TextInput::make('price')
                            ->label('Стоимость работ')
                            ->numeric()
                            ->required()
                            ->prefix('₽')
                            ->step(0.01),
                    ])->columns(2),

                Forms\Components\Section::make('Фотографии')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('before_photos')
                            ->label('Фото "До" (что принес клиент)')
                            ->collection('before_photos')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Загрузите фотографии устройства/проблемы до начала работ')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('after_photos')
                            ->label('Фото "После" (результат работ)')
                            ->collection('after_photos')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Загрузите фотографии результата работ (можно добавить позже)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('work_time_minutes')
                    ->label('Время')
                    ->formatStateUsing(fn($state) => $state . ' мин')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'order_number')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->order_number),

                Tables\Filters\Filter::make('high_price')
                    ->label('Дорогие работы')
                    ->query(fn(Builder $query): Builder => $query->where('price', '>', 2000)),

                Tables\Filters\Filter::make('long_work')
                    ->label('Долгие работы')
                    ->query(fn(Builder $query): Builder => $query->where('work_time_minutes', '>', 180)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
        return parent::getEloquentQuery();
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID ремонта')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Заказ')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('order.client.full_name')
                            ->label('Клиент'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Описание работ')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Детали работ')
                    ->schema([
                        Infolists\Components\TextEntry::make('work_time_minutes')
                            ->label('Время работы')
                            ->formatStateUsing(fn($state) => $state . ' минут')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('price')
                            ->label('Стоимость')
                            ->money('RUB')
                            ->badge()
                            ->color('success'),


                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Обновлен')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Фотографии')
                    ->schema([
                        Infolists\Components\TextEntry::make('before_photos')
                            ->label('Фото "До" (что принес клиент)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('before_photos');
                                if ($photos->count() === 0) return 'Фотографии не загружены';

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

                        Infolists\Components\TextEntry::make('after_photos')
                            ->label('Фото "После" (результат работ)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('after_photos');
                                if ($photos->count() === 0) return 'Фотографии не загружены';

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
            ]);
    }
}
