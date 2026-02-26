<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenuePlanResource\Pages;
use App\Models\Branch;
use App\Models\RevenuePlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RevenuePlanResource extends Resource
{
    protected static ?string $model = RevenuePlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Планы выручки';

    protected static ?string $modelLabel = 'План выручки';

    protected static ?string $pluralModelLabel = 'Планы выручки';

    protected static ?string $navigationGroup = 'Аналитика';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('year')
                            ->label('Год')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2024),

                        Forms\Components\Select::make('month')
                            ->label('Месяц')
                            ->required()
                            ->options(
                                collect(range(1, 12))->mapWithKeys(
                                    fn (int $month) => [
                                        $month => now()->setMonth($month)->startOfMonth()->translatedFormat('F'),
                                    ]
                                )
                            ),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->options(
                                Branch::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Пусто — общий план по всем филиалам'),
                    ]),

                Forms\Components\TextInput::make('target_amount')
                    ->label('Плановая выручка, ₽')
                    ->numeric()
                    ->required()
                    ->prefix('₽')
                    ->step(0.01)
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_label')
                    ->label('Период')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Все филиалы'),
                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Плановая выручка')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label('Год')
                    ->options(
                        RevenuePlan::query()
                            ->select('year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                    ),
            ])
            ->defaultSort('year', 'desc')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenuePlans::route('/'),
            'create' => Pages\CreateRevenuePlan::route('/create'),
            'edit' => Pages\EditRevenuePlan::route('/{record}/edit'),
        ];
    }
}

