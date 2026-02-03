<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderWork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
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
                    ->required()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('work_price')
                    ->label('Стоимость работы')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_deleted')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Удалить')
                    ->requiresConfirmation(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('is_deleted', false));
    }
}
