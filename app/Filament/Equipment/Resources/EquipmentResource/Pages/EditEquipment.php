<?php

namespace App\Filament\Equipment\Resources\EquipmentResource\Pages;

use App\Application\Equipment\Command\AddComponentCommand;
use App\Application\Equipment\Command\AddComponentHandler;
use App\Application\Equipment\Command\RegisterSerialNumberCommand;
use App\Application\Equipment\Command\RegisterSerialNumberHandler;
use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Application\Equipment\Command\UpdateEquipmentHandler;
use App\Filament\Equipment\Resources\EquipmentResource;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected static ?string $title = 'Редактирование оборудования';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['components_display'] = $this->componentsDisplayData();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addComponent')
                ->label('Добавить часть')
                ->icon(Heroicon::OutlinedPuzzlePiece)
                ->form([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->placeholder('Ручка'),
                    TextInput::make('serialNumber')
                        ->label('Серийный номер')
                        ->placeholder('Необязательно'),
                ])
                ->action(function (array $data): void {
                    try {
                        /** @var ClientEquipmentModel $record */
                        $record = $this->getRecord();
                        $componentId = app(SequentialEntityIdGenerator::class)->next('equipment_component')->value;
                        app(AddComponentHandler::class)->handle(new AddComponentCommand(
                            (int) $record->id,
                            $componentId,
                            $data['name'],
                            $data['serialNumber'] ?? null,
                        ));
                        $this->refreshComponentsDisplay();
                        Notification::make()->title('Часть добавлена')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('registerSerial')
                ->label('Указать серийный номер')
                ->icon(Heroicon::OutlinedHashtag)
                ->form([
                    Select::make('componentId')
                        ->label('Часть')
                        ->options(fn (): array => $this->getRecord()
                            ->components()
                            ->get()
                            ->mapWithKeys(static fn ($component): array => [
                                (int) $component->id => trim($component->name.(
                                    $component->serial_number ? ' · '.$component->serial_number : ' · без серийника'
                                )),
                            ])
                            ->all())
                        ->required()
                        ->searchable(),
                    TextInput::make('serialNumber')
                        ->label('Серийный номер')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        /** @var ClientEquipmentModel $record */
                        $record = $this->getRecord();
                        app(RegisterSerialNumberHandler::class)->handle(new RegisterSerialNumberCommand(
                            (int) $record->id,
                            (int) $data['componentId'],
                            $data['serialNumber'],
                        ));
                        $this->refreshComponentsDisplay();
                        Notification::make()->title('Серийный номер сохранён')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UpdateEquipmentHandler::class)->handle(new UpdateEquipmentCommand(
            (int) $record->getKey(),
            $data['title'],
            $data['brand'],
            $data['model_name'],
            (string) $data['equipment_type'],
            filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null,
            $data['notes'] ?? null,
        ));

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Оборудование обновлено';
    }

    private function refreshComponentsDisplay(): void
    {
        $this->getRecord()->refresh()->load('components');
        $this->fillForm();
    }

    /** @return list<array{id:int,name:string,serial_number:?string}> */
    private function componentsDisplayData(): array
    {
        return $this->getRecord()
            ->components()
            ->orderBy('id')
            ->get()
            ->map(static fn ($component): array => [
                'id' => (int) $component->id,
                'name' => (string) $component->name,
                'serial_number' => $component->serial_number !== null ? (string) $component->serial_number : null,
            ])
            ->all();
    }
}
