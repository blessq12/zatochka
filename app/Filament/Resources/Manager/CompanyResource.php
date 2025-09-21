<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->required()
                            ->maxLength(12)
                            ->unique(ignoreRecord: true)
                            ->mask('999999999999'),

                        Forms\Components\TextInput::make('kpp')
                            ->label('КПП')
                            ->maxLength(9)
                            ->mask('999999999'),

                        Forms\Components\TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->maxLength(15)
                            ->mask('9999999999999'),

                        Forms\Components\Textarea::make('legal_address')
                            ->label('Юридический адрес')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('website')
                            ->label('Сайт')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Логотип')
                            ->image()
                            ->directory('companies/logos')
                            ->visibility('public'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Банковские реквизиты')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Банк')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_bik')
                            ->label('БИК')
                            ->maxLength(9)
                            ->mask('999999999'),

                        Forms\Components\TextInput::make('bank_account')
                            ->label('Расчетный счет')
                            ->maxLength(20)
                            ->mask('99999999999999999999'),

                        Forms\Components\TextInput::make('bank_cor_account')
                            ->label('Корреспондентский счет')
                            ->maxLength(20)
                            ->mask('99999999999999999999'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалена')
                            ->default(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Логотип')
                    ->circular()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Юридическое название')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('kpp')
                    ->label('КПП')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('website')
                    ->label('Сайт')
                    ->searchable()
                    ->url(fn (?string $state): ?string => $state ? "https://{$state}" : null)
                    ->openUrlInNewTab()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branches_count')
                    ->label('Филиалов')
                    ->counts('branches')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все компании')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),

                Tables\Filters\Filter::make('has_branches')
                    ->label('С филиалами')
                    ->query(fn (Builder $query): Builder => $query->has('branches')),

                Tables\Filters\Filter::make('has_website')
                    ->label('С сайтом')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('website')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_branches')
                    ->label('Филиалы')
                    ->icon('heroicon-o-building-office-2')
                    ->url(fn (Company $record): string => route('filament.manager.resources.manager.branches.index', ['tableFilters[company_id][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_deleted')
                        ->label('Пометить как удаленные')
                        ->icon('heroicon-o-trash')
                        ->action(function ($records): void {
                            $records->each->update(['is_deleted' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Компании помечены как удаленные')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false; // Нельзя создавать новые компании через админку
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
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
