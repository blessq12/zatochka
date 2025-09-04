<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\CompanyResource\Pages;
use App\Domain\Company\Services\CompanyService;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class CompanyResource extends Resource
{
    protected static ?string $model = \App\Models\Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Управление компанией';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Компании';

    protected static ?string $modelLabel = 'Компания';

    protected static ?string $pluralModelLabel = 'Компании';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        Forms\Components\TextInput::make('legal_name')
                            ->label('Юридическое название')
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->required()
                            ->maxLength(12)
                            ->rules(['required', 'string', 'max:12', 'regex:/^\d{10,12}$/'])
                            ->helperText('ИНН должен содержать 10 или 12 цифр'),

                        Forms\Components\TextInput::make('kpp')
                            ->label('КПП')
                            ->maxLength(9)
                            ->rules(['nullable', 'string', 'max:9', 'regex:/^\d{9}$/']),

                        Forms\Components\TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->maxLength(15)
                            ->rules(['nullable', 'string', 'max:15', 'regex:/^\d{13,15}$/']),

                        Forms\Components\TextInput::make('legal_address')
                            ->label('Юридический адрес')
                            ->required()
                            ->maxLength(500)
                            ->rules(['required', 'string', 'max:500']),
                    ])->columns(2),

                Forms\Components\Section::make('Реквизиты')
                    ->schema([
                        Forms\Components\TextInput::make('bank_account')
                            ->label('Расчетный счет')
                            ->maxLength(20)
                            ->rules(['nullable', 'string', 'max:20', 'regex:/^\d{20}$/']),

                        Forms\Components\TextInput::make('bank_name')
                            ->label('Банк')
                            ->maxLength(255)
                            ->rules(['nullable', 'string', 'max:255']),

                        Forms\Components\TextInput::make('bank_bik')
                            ->label('БИК')
                            ->maxLength(9)
                            ->rules(['nullable', 'string', 'max:9', 'regex:/^\d{9}$/']),

                        Forms\Components\TextInput::make('bank_cor_account')
                            ->label('Корр. счет')
                            ->maxLength(20)
                            ->rules(['nullable', 'string', 'max:20', 'regex:/^\d{20}$/']),
                    ])->columns(2),

                Forms\Components\Section::make('Контактная информация')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(20)
                            ->rules(['nullable', 'string', 'max:20']),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->rules(['nullable', 'email', 'max:255']),

                        Forms\Components\TextInput::make('website')
                            ->label('Сайт')
                            ->url()
                            ->maxLength(255)
                            ->rules(['nullable', 'url', 'max:255']),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(1000)
                            ->rules(['nullable', 'string', 'max:1000']),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительные данные')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_data')
                            ->label('Дополнительные данные')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение')
                            ->rules(['nullable', 'array']),
                    ]),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->rules(['boolean']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Юридическое название')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('legal_address')
                    ->label('Адрес')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус активности')
                    ->placeholder('Все компании')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('has_branches')
                    ->label('С филиалами')
                    ->query(fn(Builder $query): Builder => $query->whereHas('branches')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('Активировать')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_active)
                    ->action(function ($record) {
                        try {
                            app(CompanyService::class)->activateCompany($record->id);
                            Notification::make()
                                ->title('Компания активирована')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка активации')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('deactivate')
                    ->label('Деактивировать')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->is_active)
                    ->action(function ($record) {
                        try {
                            app(CompanyService::class)->deactivateCompany($record->id);
                            Notification::make()
                                ->title('Компания деактивирована')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка деактивации')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
            'view' => Pages\ViewCompany::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
