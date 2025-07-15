<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RepairsRelationManager extends RelationManager
{
    protected static string $relationship = 'repairs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('handle_number')
                    ->label('Номер ручки/блока')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Описание работ')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('cost')
                    ->label('Стоимость')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'В ожидании',
                        'in_progress' => 'В работе',
                        'completed' => 'Завершен',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('handle_number')
                    ->label('Номер ручки/блока')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание работ')
                    ->limit(50),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Стоимость')
                    ->money('RUB'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'В ожидании',
                        'in_progress' => 'В работе',
                        'completed' => 'Завершен',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
