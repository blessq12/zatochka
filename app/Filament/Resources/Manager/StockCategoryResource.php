<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\StockCategoryResource\Pages;
use App\Models\StockCategory as StockCategoryModel;
// ... existing code ...
use App\Domain\Inventory\ValueObjects\CategoryName;
use App\Domain\Inventory\Services\StockCategoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StockCategoryResource extends Resource
{
    protected static ?string $model = StockCategoryModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Склад';
    protected static ?string $navigationLabel = 'Категории';
    protected static ?string $modelLabel = 'Категория';
    protected static ?string $pluralModelLabel = 'Категории';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название категории')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Расходники'),

                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->placeholder('Описание категории товаров'),

                        ColorPicker::make('color')
                            ->label('Цвет')
                            ->helperText('Цвет для визуального выделения категории'),

                        TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->helperText('Меньшее число = выше в списке'),
                    ])
                    ->columns(2),

                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->helperText('Активные категории доступны для товаров'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),

                ColorColumn::make('color')
                    ->label('Цвет'),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable()
                    ->badge(),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'Активна',
                        'danger' => 'Неактивна',
                    ])
                    ->getStateUsing(
                        fn(Model $record): string =>
                        $record->is_active ? 'Активна' : 'Неактивна'
                    ),

                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все категории')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (array $data, Model $record): Model {
                        $categoryService = app(StockCategoryService::class);

                        $categoryId = (int) $record->id;
                        $name = CategoryName::fromString($data['name']);

                        if ($data['is_active'] && !$record->is_active) {
                            $categoryService->activateCategory($categoryId);
                        } elseif (!$data['is_active'] && $record->is_active) {
                            $categoryService->deactivateCategory($categoryId);
                        }

                        if ($data['name'] !== $record->name) {
                            $categoryService->updateCategory($categoryId, name: $name);
                        }

                        if ($data['description'] !== $record->description) {
                            $categoryService->updateCategory($categoryId, description: $data['description']);
                        }

                        if ($data['color'] !== $record->color) {
                            $categoryService->updateCategory($categoryId, color: $data['color']);
                        }

                        if ($data['sort_order'] !== $record->sort_order) {
                            $categoryService->updateCategory($categoryId, sortOrder: $data['sort_order']);
                        }

                        Notification::make()
                            ->title('Категория обновлена')
                            ->success()
                            ->send();

                        return $record->fresh();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record): void {
                        $categoryService = app(StockCategoryService::class);
                        $categoryId = (int) $record->id;

                        $categoryService->deleteCategory($categoryId);

                        Notification::make()
                            ->title('Категория удалена')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function (array $records): void {
                            $categoryService = app(StockCategoryService::class);

                            foreach ($records as $record) {
                                $categoryId = (int) $record->id;
                                $categoryService->deleteCategory($categoryId);
                            }

                            Notification::make()
                                ->title('Категории удалены')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockCategories::route('/'),
            'create' => Pages\CreateStockCategory::route('/create'),
            'edit' => Pages\EditStockCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_deleted', false);
    }
}
