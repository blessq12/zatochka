<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\ClientResource\Pages;
use App\Filament\Resources\Manager\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Заказы';
    protected static ?string $pluralLabel = 'Клиенты';
    protected static ?string $modelLabel = 'Клиент';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->mask('+7 (999) 999-99-99')
                            ->placeholder('+7 (###) ###-##-##')
                            ->rules(['regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'])
                            ->maxLength(20),

                        Forms\Components\TextInput::make('telegram')
                            ->label('Telegram')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->displayFormat('d.m.Y'),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->searchable()
                    ->copyable()
                    ->formatStateUsing(function ($state) {
                        return '@' . $state;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),


                Tables\Columns\TextColumn::make('is_deleted')
                    ->label('Статус')
                    ->formatStateUsing(fn($state) => $state ? 'Удален' : 'Активен')
                    ->badge()
                    ->color(fn($state) => $state ? 'danger' : 'success'),

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Удаленные')
                    ->falseLabel('Активные')
                    ->queries(
                        true: fn(Builder $query) => $query->where('is_deleted', true),
                        false: fn(Builder $query) => $query->where('is_deleted', false),
                    ),
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
            // RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
