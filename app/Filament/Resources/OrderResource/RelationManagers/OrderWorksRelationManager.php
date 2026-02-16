<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderWork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderWorksRelationManager extends RelationManager
{
    protected static string $relationship = 'orderWorks';

    protected static ?string $title = 'Работы';

    protected static ?string $modelLabel = 'Работа';

    protected static ?string $pluralModelLabel = 'Работы';

    public function form(Form $form): Form
    {
        $order = $this->getOwnerRecord();
        $equipment = $order->equipment;
        $hasMultipleComponents = $equipment && 
            is_array($equipment->serial_number) && 
            count($equipment->serial_number) > 1;

        $componentOptions = [];
        if ($hasMultipleComponents) {
            foreach ($equipment->serial_number as $component) {
                $name = trim($component['name'] ?? '');
                $sn = trim($component['serial_number'] ?? '');
                $label = $name ?: 'Элемент';
                if ($sn) {
                    $label .= " (SN: {$sn})";
                }
                $componentOptions[$name] = $label;
            }
        }

        return $form
            ->schema([
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Select::make('equipment_component_name')
                    ->label('Элемент оборудования')
                    ->options($componentOptions)
                    ->nullable()
                    ->visible(fn() => $hasMultipleComponents)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, $get) use ($equipment) {
                        if ($state && $equipment && is_array($equipment->serial_number)) {
                            foreach ($equipment->serial_number as $component) {
                                if (trim($component['name'] ?? '') === $state) {
                                    $set('equipment_component_serial_number', trim($component['serial_number'] ?? ''));
                                    break;
                                }
                            }
                        } else {
                            $set('equipment_component_serial_number', null);
                        }
                    }),

                Forms\Components\TextInput::make('equipment_component_serial_number')
                    ->label('Серийный номер элемента')
                    ->disabled()
                    ->dehydrated()
                    ->visible(fn($get) => $get('equipment_component_name') !== null),

                Forms\Components\TextInput::make('work_price')
                    ->label('Стоимость работы')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->required()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('equipment_component_name')
                    ->label('Элемент оборудования')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('equipment_component_serial_number')
                    ->label('Серийный номер элемента')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('work_price')
                    ->label('Стоимость работы')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_deleted')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Удалить')
                    ->requiresConfirmation(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('is_deleted', false));
    }
}
