<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\CompanyResource\Pages;
use App\Filament\Resources\Manager\CompanyResource\RelationManagers;
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
    protected static ?string $navigationGroup = 'Компания';
    protected static ?string $pluralLabel = 'Компании';
    protected static ?string $modelLabel = 'Компания';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('legal_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('inn')
                    ->required()
                    ->maxLength(12),
                Forms\Components\TextInput::make('kpp')
                    ->maxLength(9),
                Forms\Components\TextInput::make('ogrn')
                    ->maxLength(15),
                Forms\Components\Textarea::make('legal_address')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_bik')
                    ->maxLength(9),
                Forms\Components\TextInput::make('bank_account')
                    ->maxLength(20),
                Forms\Components\TextInput::make('bank_cor_account')
                    ->maxLength(20),
                Forms\Components\TextInput::make('logo_path')
                    ->maxLength(255),
                Forms\Components\TextInput::make('additional_data'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_deleted')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('legal_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kpp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ogrn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_bik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_cor_account')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo_path')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
