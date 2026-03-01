<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ReviewResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Текст')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Одобрен')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Виден')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Редактировать')
                    ->url(fn ($record) => ReviewResource::getUrl('edit', ['record' => $record])),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
