<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Компании';
    
    protected static ?string $modelLabel = 'Компания';
    
    protected static ?string $pluralModelLabel = 'Компании';
    
    protected static ?string $navigationGroup = 'Организация';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('legal_name')
                            ->label('Юридическое название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Реквизиты')
                    ->schema([
                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->required()
                            ->maxLength(12),
                        Forms\Components\TextInput::make('kpp')
                            ->label('КПП')
                            ->maxLength(9),
                        Forms\Components\TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->maxLength(15),
                        Forms\Components\Textarea::make('legal_address')
                            ->label('Юридический адрес')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Банковские реквизиты')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Название банка')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_bik')
                            ->label('БИК')
                            ->maxLength(9),
                        Forms\Components\TextInput::make('bank_account')
                            ->label('Расчетный счет')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('bank_cor_account')
                            ->label('Корреспондентский счет')
                            ->maxLength(20),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалена')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Юридическое название')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branches_count')
                    ->label('Филиалов')
                    ->counts('branches')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все компании')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все компании')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
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
            'index' => Pages\ViewCompany::route('/{record?}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
