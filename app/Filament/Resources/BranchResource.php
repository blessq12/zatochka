<?php

namespace App\Filament\Resources;

use App\Models\Branch;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\BranchResource\Pages;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Филиал';
    protected static ?string $pluralModelLabel = 'Филиалы';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Section::make('Основная информация')
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Компания')
                            ->required(),
                        TextInput::make('name')
                            ->label('Название')
                            ->required(),
                        TextInput::make('code')
                            ->label('Код филиала')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])->columnSpan(1),

                Section::make('Контактная информация')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        Textarea::make('address')
                            ->label('Адрес')
                            ->required(),
                        TextInput::make('working_hours')
                            ->label('Часы работы'),
                    ])->columnSpan(1),
            ]),

            Section::make('Геолокация')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('latitude')
                            ->label('Широта')
                            ->numeric()
                            ->rules(['regex:/^[-]?((([0-8]?[0-9])\.(\d+))|(90(\.0+)?))$/']),
                        TextInput::make('longitude')
                            ->label('Долгота')
                            ->numeric()
                            ->rules(['regex:/^[-]?((([0-9]?[0-9]|1[0-7][0-9])\.(\d+))|(180(\.0+)?))$/']),
                    ]),
                ]),

            Section::make('Дополнительно')
                ->schema([
                    Textarea::make('description')
                        ->label('Описание')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Компания')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Адрес')
                    ->limit(30),
                TextColumn::make('phone')
                    ->label('Телефон'),
                ToggleColumn::make('is_active')
                    ->label('Активен'),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
