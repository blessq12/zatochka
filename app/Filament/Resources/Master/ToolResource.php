<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\ToolResource\Pages;
use App\Models\Tool;
use App\Models\EquipmentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Оборудование';

    protected static ?string $modelLabel = 'Оборудование';

    protected static ?string $pluralModelLabel = 'Оборудование';

    protected static ?string $navigationGroup = 'Ремонтная мастерская';

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

                        Forms\Components\Select::make('equipment_type_id')
                            ->label('Тип оборудования')
                            ->relationship('equipmentType', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название типа')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('brand')
                            ->label('Бренд')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Серийный номер')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Дата покупки')
                            ->displayFormat('d.m.Y'),

                        Forms\Components\DatePicker::make('warranty_expiry')
                            ->label('Гарантия до')
                            ->displayFormat('d.m.Y'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активно')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Медиафайлы')
                    ->schema([
                        Forms\Components\FileUpload::make('photos')
                            ->label('Фото оборудования')
                            ->image()
                            ->multiple()
                            ->directory('tools/photos')
                            ->visibility('private')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('manuals')
                            ->label('Инструкции')
                            ->acceptedFileTypes(['application/pdf'])
                            ->multiple()
                            ->directory('tools/manuals')
                            ->visibility('private')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('warranty_documents')
                            ->label('Гарантийные документы')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->multiple()
                            ->directory('tools/warranty')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ])
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
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('equipmentType.name')
                    ->label('Тип')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Бренд')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Модель')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Серийный номер')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('warranty_expiry')
                    ->label('Гарантия')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn($state) => $state && $state < now() ? 'danger' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('photos')
                    ->label('Фото')
                    ->boolean()
                    ->getStateUsing(fn(Tool $record): bool => $record->getMedia('photos')->count() > 0),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('equipment_type_id')
                    ->label('Тип оборудования')
                    ->relationship('equipmentType', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все оборудование')
                    ->trueLabel('Только активное')
                    ->falseLabel('Только неактивное'),

                Tables\Filters\Filter::make('warranty_expired')
                    ->label('Гарантия истекла')
                    ->query(fn(Builder $query): Builder => $query->where('warranty_expiry', '<', now())),

                Tables\Filters\Filter::make('has_photos')
                    ->label('С фото')
                    ->query(fn(Builder $query): Builder => $query->whereHas('media')),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удалено')
                    ->placeholder('Все оборудование')
                    ->trueLabel('Только удаленное')
                    ->falseLabel('Только активное'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label('Активировать/Деактивировать')
                    ->icon('heroicon-o-power')
                    ->color(fn(Tool $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Tool $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        \Filament\Notifications\Notification::make()
                            ->title($record->is_active ? 'Оборудование активировано' : 'Оборудование деактивировано')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records): void {
                            $records->each->update(['is_active' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Оборудование активировано')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records): void {
                            $records->each->update(['is_active' => false]);
                            \Filament\Notifications\Notification::make()
                                ->title('Оборудование деактивировано')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'view' => Pages\ViewTool::route('/{record}'),
            'edit' => Pages\EditTool::route('/{record}/edit'),
        ];
    }
}
