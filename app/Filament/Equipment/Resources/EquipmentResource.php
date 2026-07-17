<?php

namespace App\Filament\Equipment\Resources;

use App\Filament\Equipment\Resources\EquipmentResource\Pages\CreateEquipment;
use App\Filament\Equipment\Resources\EquipmentResource\Pages\EditEquipment;
use App\Filament\Equipment\Resources\EquipmentResource\Pages\ListEquipments;
use App\Filament\Support\ClientSelectField;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EquipmentResource extends DomainResource
{
    protected static ?string $model = ClientEquipmentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Оборудование';

    protected static ?string $navigationLabel = 'Оборудование';

    protected static ?string $modelLabel = 'Оборудование';

    protected static ?string $pluralModelLabel = 'Оборудование';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('components');
    }

    public static function clientSelect(): Select
    {
        return ClientSelectField::make()
            ->nullable()
            ->placeholder('Не выбран');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Название')
                ->required()
                ->maxLength(255),
            TextInput::make('brand')
                ->label('Бренд')
                ->required()
                ->maxLength(255),
            TextInput::make('model_name')
                ->label('Модель')
                ->required()
                ->maxLength(255),
            static::clientSelect(),
            TextInput::make('notes')
                ->label('Заметки'),
            Repeater::make('parts')
                ->label('Части оборудования')
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->placeholder('Ручка / Блок управления / Блок питания'),
                    TextInput::make('serialNumber')
                        ->label('Серийный номер')
                        ->placeholder('Необязательно'),
                ])
                ->defaultItems(0)
                ->addActionLabel('Добавить часть')
                ->columns(2)
                ->visible(fn (string $operation): bool => $operation === 'create'),
            Repeater::make('components_display')
                ->label('Части оборудования')
                ->schema([
                    TextInput::make('id')
                        ->label('№')
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Название')
                        ->disabled(),
                    TextInput::make('serial_number')
                        ->label('Серийный номер')
                        ->disabled()
                        ->placeholder('—'),
                ])
                ->columns(3)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->dehydrated(false)
                ->visible(fn (string $operation): bool => $operation === 'edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        $clients = ClientSelectField::options();

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('brand')
                    ->label('Бренд')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model_name')
                    ->label('Модель')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->formatStateUsing(fn (?int $state) => $state !== null ? ($clients[$state] ?? ('#'.$state)) : '—')
                    ->sortable(),
                TextColumn::make('components_count')
                    ->counts('components')
                    ->label('Частей'),
                TextColumn::make('notes')
                    ->label('Заметки')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipments::route('/'),
            'create' => CreateEquipment::route('/create'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}
