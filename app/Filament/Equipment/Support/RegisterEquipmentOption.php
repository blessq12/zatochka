<?php

namespace App\Filament\Equipment\Support;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Equipment\DTO\EquipmentPartDTO;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Equipment\VO\EquipmentType;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;

/**
 * Регистрация оборудования «на лету» из чужих форм (createOption у Select).
 * UI-владение остаётся за Equipment: форма и команда живут здесь.
 */
final class RegisterEquipmentOption
{
    /**
     * @param  Closure(): int  $clientIdResolver  возвращает id выбранного клиента (0 — не выбран)
     */
    public static function applyTo(Select $select, Closure $clientIdResolver): Select
    {
        return $select
            ->createOptionForm(self::form())
            ->createOptionUsing(fn (array $data): int => self::register($data, $clientIdResolver()))
            ->createOptionAction(fn (Action $action): Action => $action
                ->label('Новое оборудование')
                ->modalHeading('Новое оборудование')
                ->modalSubmitActionLabel('Зарегистрировать')
                ->button()
                ->outlined()
                ->color('primary')
                ->icon(null)
                ->disabled(fn (Get $get): bool => blank($get('client_id'))));
    }

    /** @return list<Field> */
    public static function form(): array
    {
        return [
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
            self::partsRepeater(),
        ];
    }

    public static function partsRepeater(string $name = 'parts'): Repeater
    {
        return Repeater::make($name)
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
            ->defaultItems(1)
            ->minItems(1)
            ->required()
            ->helperText('Нужна хотя бы одна часть или элемент')
            ->validationMessages([
                'min' => 'Добавьте хотя бы одну часть или элемент оборудования.',
                'required' => 'Добавьте хотя бы одну часть или элемент оборудования.',
            ])
            ->addActionLabel('Добавить часть')
            ->columns(2)
            ->columnSpanFull()
            ->collapsible();
    }

    /** @param array<string, mixed> $data */
    public static function register(array $data, int $clientId): int
    {
        if ($clientId <= 0) {
            throw ValidationException::withMessages([
                'data.client_id' => 'Сначала выберите клиента.',
            ]);
        }

        $ids = app(EntityIdGenerator::class);
        $equipmentId = $ids->next('equipment')->value;
        $parts = [];

        foreach ($data['parts'] ?? [] as $part) {
            if (! filled($part['name'] ?? null)) {
                continue;
            }

            $parts[] = new EquipmentPartDTO(
                $ids->next('equipment_component')->value,
                (string) $part['name'],
                filled($part['serialNumber'] ?? null)
                    ? (string) $part['serialNumber']
                    : null,
            );
        }

        if ($parts === []) {
            throw ValidationException::withMessages([
                'data.parts' => 'Добавьте хотя бы одну часть или элемент оборудования.',
            ]);
        }

        app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            $equipmentId,
            (string) $data['title'],
            (string) $data['brand'],
            (string) $data['model_name'],
            (string) $data['equipment_type'],
            $clientId,
            $parts,
        ));

        return $equipmentId;
    }
}
