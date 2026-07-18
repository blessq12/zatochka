<?php

namespace App\Filament\Equipment\Resources;

use App\Domain\Equipment\VO\EquipmentType;
use App\Filament\Equipment\Resources\EquipmentResource\Pages\CreateEquipment;
use App\Filament\Equipment\Resources\EquipmentResource\Pages\EditEquipment;
use App\Filament\Equipment\Resources\EquipmentResource\Pages\ListEquipments;
use App\Filament\Equipment\Support\EquipmentPresentation;
use App\Filament\Equipment\Support\RegisterEquipmentOption;
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
use Filament\Tables\Enums\RecordActionsPosition;
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

    protected static ?string $recordTitleAttribute = 'number';

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
        return parent::getEloquentQuery()->with(['components', 'client']);
    }

    public static function clientSelect(): Select
    {
        return ClientSelectField::make()
            ->required()
            ->helperText(null);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->label('Номер')
                ->disabled()
                ->dehydrated(false)
                ->visible(fn (string $operation): bool => $operation === 'edit'),
            TextInput::make('title')
                ->label('Название')
                ->required()
                ->maxLength(255),
            Select::make('equipment_type')
                ->label('Тип оборудования')
                ->options(EquipmentType::options())
                ->required()
                ->native(false)
                ->searchable(),
            TextInput::make('brand')
                ->label('Бренд')
                ->required()
                ->maxLength(255),
            TextInput::make('model_name')
                ->label('Модель')
                ->required()
                ->maxLength(255),
            static::clientSelect(),
            RegisterEquipmentOption::partsRepeater()
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
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment_type')
                    ->label('Тип')
                    ->formatStateUsing(fn (?string $state): string => EquipmentType::tryLabel($state) ?? '—')
                    ->sortable(),
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
                    ->formatStateUsing(fn (?int $state, ClientEquipmentModel $record): string => EquipmentPresentation::clientListingName($record))
                    ->description(fn (ClientEquipmentModel $record): string => EquipmentPresentation::clientListingPhone($record))
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $client) use ($search): void {
                            $client->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('components_count')
                    ->counts('components')
                    ->label('Частей')
                    ->alignCenter(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconButton()
                    ->tooltip('Редактировать'),
            ], RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('');
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
