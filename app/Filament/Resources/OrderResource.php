<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Заказы';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('ФИО')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel(),
                                Forms\Components\TextInput::make('telegram')
                                    ->label('Telegram'),
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'new' => 'Новый',
                                'in_progress' => 'В работе',
                                'ready' => 'Готов',
                                'delivered' => 'Доставлен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Сумма')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Себестоимость')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('profit')
                            ->label('Прибыль')
                            ->numeric()
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'in_progress' => 'warning',
                        'ready' => 'success',
                        'delivered' => 'info',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Себестоимость')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Прибыль')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новый',
                        'in_progress' => 'В работе',
                        'ready' => 'Готов',
                        'delivered' => 'Доставлен',
                        'cancelled' => 'Отменен',
                    ]),
                Tables\Filters\SelectFilter::make('client')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderToolsRelationManager::class,
            RelationManagers\RepairsRelationManager::class,
            RelationManagers\NotificationsRelationManager::class,
            RelationManagers\FeedbackRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
