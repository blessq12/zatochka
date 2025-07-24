<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Отзывы';
    protected static ?string $breadcrumb = 'Отзывы';
    protected static ?int $navigationSort = 6;
    protected static ?string $title = 'Custom Page Title';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Текст отзыва')
                            ->required()
                            ->maxLength(65535),
                        Forms\Components\Select::make('rating')
                            ->label('Оценка')
                            ->options([
                                1 => '1 - Ужасно',
                                2 => '2 - Плохо',
                                3 => '3 - Нормально',
                                4 => '4 - Хорошо',
                                5 => '5 - Отлично',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Номер заказа')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Текст отзыва')
                    ->limit(50),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Оценка')
                    ->options([
                        1 => '1 - Ужасно',
                        2 => '2 - Плохо',
                        3 => '3 - Нормально',
                        4 => '4 - Хорошо',
                        5 => '5 - Отлично',
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
