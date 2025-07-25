<?php

namespace App\Filament\Resources;

use App\Models\Company;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use App\Filament\Resources\CompanyResource\Pages;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Компания';
    protected static ?string $pluralModelLabel = 'Компании';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->required(),
                        TextInput::make('legal_name')
                            ->label('Юридическое название'),
                        TextInput::make('inn')
                            ->label('ИНН')
                            ->length(12),
                        TextInput::make('kpp')
                            ->label('КПП')
                            ->length(9),
                        TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->length(15),
                        TextInput::make('website')
                            ->label('Веб-сайт')
                            ->url(),
                    ])->columnSpan(1),

                Section::make('Юридическая информация')
                    ->schema([
                        Textarea::make('legal_address')
                            ->label('Юридический адрес'),
                        FileUpload::make('logo_path')
                            ->label('Логотип')
                            ->image()
                            ->directory('companies/logos'),
                        Textarea::make('description')
                            ->label('Описание компании')
                            ->rows(3),
                    ])->columnSpan(1),
            ]),

            Section::make('Банковские реквизиты')
                ->schema([
                    TextInput::make('bank_name')
                        ->label('Название банка'),
                    TextInput::make('bank_bik')
                        ->label('БИК')
                        ->length(9),
                    TextInput::make('bank_account')
                        ->label('Расчетный счет')
                        ->length(20),
                    TextInput::make('bank_cor_account')
                        ->label('Корреспондентский счет')
                        ->length(20),
                ]),

            Section::make('Дополнительные данные')
                ->schema([
                    TextInput::make('additional_data.short_legal_name')
                        ->label('Сокращенное юр. название')
                        ->placeholder('ИП Иванов И.И.'),
                    TextInput::make('additional_data.bank_inn')
                        ->label('ИНН банка')
                        ->length(10),
                    TextInput::make('additional_data.bank_kpp')
                        ->label('КПП банка')
                        ->length(9),
                    TextInput::make('additional_data.account_open_date')
                        ->label('Дата открытия счета')
                        ->placeholder('дд.мм.гггг'),
                    KeyValue::make('additional_data.custom_fields')
                        ->label('Дополнительные поля')
                        ->keyLabel('Название')
                        ->valueLabel('Значение')
                        ->addable()
                        ->deletable()
                        ->reorderable(),
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Логотип'),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('legal_name')
                    ->label('Юр. название')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable(),
                TextColumn::make('website')
                    ->label('Сайт')
                    ->url(fn(?string $state): ?string => $state),
                TextColumn::make('additional_data.short_legal_name')
                    ->label('Сокр. юр. название'),
            ])
            ->defaultSort('name', 'asc');
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
